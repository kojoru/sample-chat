((window, document, undefined) => {

    let loadingNumber = 0;
    let elements = {};
    let users = [];
    let currentUserId;
    let currentTimeout;

    const hide = (element) => {
        element.classList.add('hidden');
    };

    const show = (element) => {
        element.classList.remove('hidden');
    };

    const startLoading = () => {
        loadingNumber++;
        show(elements.loadingOverlay);
    };

    const stopLoading = () => {
        if (--loadingNumber <= 0) {
            hide(elements.loadingOverlay);
        }
    };

    const fetchLogin = (userName) => {
        return fetch('/login', {
            method: 'POST',
            body: JSON.stringify({
                name: userName,
            }),
            headers: new Headers({
                'Content-Type': 'application/json'
            })
        })
            .then(response => response.json());
    };

    const fetchUsers = () => {
        return fetch('/user', {
            headers: new Headers({
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            })
        })
            .then(response => response.json());
    };

    const fetchNewMessage = (toUserId, value) => {
        return fetch('/message', {
            method: 'POST',
            body: JSON.stringify({
                toUserId: toUserId,
                value: value
            }),
            headers: new Headers({
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            })
        })
            .then(response => response.json());
    };

    const fetchMessages = (userId, beforeDate, afterDate) => {
        const url = `/message?user_id=${userId}${beforeDate ? '&before_date=' + encodeURIComponent(beforeDate) : ''}${afterDate ? '&after_date=' + encodeURIComponent(afterDate) : ''}`;
        return fetch(url, {
            method: 'GET',
            headers: new Headers({
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            })
        })
            .then(response => response.json());
    };

    const saveLogin = (loginData) => {
        window.localStorage.setItem('login', JSON.stringify(loginData));
        return loginData;
    };

    const removeLogin = () => {
        window.localStorage.removeItem('login');
    };

    const getSavedLogin = () => {
        const json = window.localStorage.getItem('login');
        if (!json) return undefined;
        try {
            return JSON.parse(json);
        } catch (e) {
            // malformed data in storage
            removeLogin();
            return undefined;
        }
    };

    const getToken = () => {
        return getSavedLogin().token;
    };

    const login = (userName) => {
        startLoading();
        fetchLogin(userName)
            .then(saveLogin)
            .then(() => {
                stopLoading();
                return initChat();
            });
    };

    const addMessages = (result, addToEnd) => {
        let lastDate = undefined;
        let d = document.createDocumentFragment();
        result.messages.forEach(message => {
            lastDate = message.date;
            const newNode = elements.chatMessage.cloneNode(true);
            newNode.childNodes.forEach(node => {
                if (node.classList) {
                    if (node.classList.contains('js-author')) {
                        node.textContent = `${users[message.fromUserId].name}:`;
                    } else if (node.classList.contains('js-message-text')) {
                        node.textContent = message.value;
                    }
                }
            });
            d.insertBefore(newNode, d.firstChild);
        });
        if (addToEnd) {
            elements.messageList.appendChild(d);
        } else {
            elements.messageList.insertBefore(d, elements.messageList.firstChild);
        }

        if (result.messages.length > 0) {
            elements.messageList.scrollTop = elements.messageList.scrollHeight;
        }
        if (result.hasMore) {
            fetchMessages(currentUserId, lastDate).then(addMessages);
        }
    };

    const selectInterlocutor = (userId) => {
        document.querySelectorAll('.js-user').forEach(node => node.classList.remove('user-list__user_selected'));
        document.querySelector(`div[data-id="${userId}"]`).classList.add('user-list__user_selected');

        currentUserId = userId;
        startLoading();
        fetchMessages(userId).then(result => {
            elements.messageList.innerHTML = '';
            addMessages(result);
            setupWatcher(result.messages);
            stopLoading();
        });
    };

    const setNoInterlocutorZeroCase = () => {
        elements.chatMessage.textContent = 'You are the first one here. Invite someone to chat with them.';
        hide(elements.chatForm);
    };

    const setupWatcher = (messages) => {
        clearTimeout(currentTimeout);
        currentTimeout = setTimeout((messages) => {
            fetchMessages(currentUserId, undefined, messages[0] ? messages[0].date : null).then((result) => {
                if (result.messages && result.messages.length > 0) {
                    addMessages(result, true);
                    setupWatcher(result.messages);
                } else {
                    setupWatcher(messages);
                }
            });
        }, 500, messages);
    };

    const attachEvents = () => {
        elements.loginForm.addEventListener('submit', event => {
            event.preventDefault();

            login(event.target.elements['login'].value);
        });

        elements.logoutButton.addEventListener('click', event => {
            event.preventDefault();
            removeLogin();
            clearTimeout(currentTimeout);
            hide(elements.chatPage);
            show(elements.landingPage);
        });

        elements.chatForm.addEventListener('submit', event => {
            event.preventDefault();
            fetchNewMessage(currentUserId, event.target.elements['chat-message'].value);

            event.target.elements['chat-message'].value = '';
        })
    };

    const initChat = () => {
        const loginData = getSavedLogin();
        if (loginData) {
            startLoading();
            show(elements.chatForm);
            elements.username.textContent = `You are ${loginData.name}`;
            return fetchUsers().then((usersData) => {
                elements.userList.innerHTML = '';
                users = [];
                users[loginData.id] = loginData;
                usersData.users.forEach(user => {
                    users[user.id] = user;
                    if (user.isCurrentUser) return;
                    const newNode = elements.userTemplate.cloneNode(true);
                    newNode.textContent = user.name;
                    newNode.dataset["id"] = user.id;
                    newNode.addEventListener('click', event => {
                        let id = event.target.dataset["id"];
                        selectInterlocutor(id);
                    });
                    elements.userList.appendChild(newNode);

                });

                let usersExceptCurrent = usersData.users.filter(user => !user.isCurrentUser);
                if (usersExceptCurrent[0]) {
                    selectInterlocutor(usersExceptCurrent[0].id);
                } else {
                    setNoInterlocutorZeroCase();
                }
                show(elements.chatPage);
                hide(elements.landingPage);
                stopLoading();
            });
        } else {
            hide(elements.chatPage);
            show(elements.landingPage);
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        elements = {
            loadingOverlay: document.querySelector('.js-loading-overlay'),
            chatPage: document.querySelector('.js-chat'),
            landingPage: document.querySelector('.js-landing'),
            username: document.querySelector('.js-username'),
            userTemplate: document.querySelector('.js-user'),
            userList: document.querySelector('.js-user-list'),
            logoutButton: document.querySelector('.js-logout'),
            loginForm: document.querySelector('.js-login-form'),
            chatForm: document.querySelector('.js-chat-form'),
            chatMessage: document.querySelector('.js-message'),
            messageList: document.querySelector('.js-message-list')

        };
        attachEvents();
        initChat();
    });

})(window, document);
