((window, document, undefined) => {

    let loadingNumber = 0;
    let elements = {};
    let users = [];
    let currentUserId;

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

    const selectInterlocutor = (id) => {
        currentUserId = id;
        elements.chatMessage.textContent = `You are now talking to ${users[id].name} (write-only)`;
    };

    const attachEvents = () => {
        elements.loginForm.addEventListener('submit', event => {
            console.log('submit');
            event.preventDefault();

            login(event.target.elements['login'].value);
        });

        elements.logoutButton.addEventListener('click', event => {
            event.preventDefault();
            removeLogin();
            hide(elements.chatPage);
            show(elements.landingPage);
        });

        elements.chatForm.addEventListener('submit', event => {
            event.preventDefault();
            fetchNewMessage(currentUserId, event.target.elements['chat-message'].value);
        })
    };

    const initChat = () => {
        const loginData = getSavedLogin();
        if (loginData) {
            startLoading();
            elements.username.textContent = `You are ${loginData.name}`;
            return fetchUsers().then((usersData) => {
                elements.userList.innerHTML = '';
                users = [];
                usersData.users.forEach(user => {
                    users[user.id] = user;
                    const newNode = elements.userTemplate.cloneNode(true);
                    newNode.textContent = user.name;
                    newNode.dataset["id"] = user.id;
                    newNode.addEventListener('click', event => {
                        let id = event.target.dataset["id"];
                        selectInterlocutor(id);
                    });
                    elements.userList.appendChild(newNode);

                });
                selectInterlocutor(usersData.users[0].id);
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
            chatMessage: document.querySelector('.js-message')

        };
        attachEvents();
        initChat();
    });

})(window, document);