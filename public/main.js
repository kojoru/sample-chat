((window, document, undefined) => {

    let loadingNumber = 0;
    let elements = {};

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

    const fetchUsers = (loginData) => {
        return fetch('/user', {
            headers: new Headers({
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${loginData.token}`
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

    const login = (userName) => {
        startLoading();
        fetchLogin(userName)
            .then(saveLogin)
            .then(() => {
                stopLoading();
                return initChat();
            });
    };

    const attachEvents = () => {
        document.querySelector('.js-login-form').addEventListener('submit', event => {
            console.log('submit');
            event.preventDefault();

            login(event.target.elements['login'].value);
        });

        document.querySelector('.js-logout').addEventListener('click', event => {
            event.preventDefault();
            removeLogin();
            hide(elements.chatPage);
            show(elements.landingPage);
        })
    };

    const initChat = () => {
        const loginData = getSavedLogin();
        if (loginData) {
            startLoading();
            elements.username.textContent = `You are ${loginData.name}`;
            return fetchUsers(loginData).then((usersData) => {
                elements.userList.innerHTML = '';
                usersData.users.forEach(user => {
                    const newNode = elements.userTemplate.cloneNode(true);
                    newNode.textContent = user.name;
                    elements.userList.appendChild(newNode);

                });
                console.log(usersData);
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
            userList: document.querySelector('.js-user-list')
        };
        attachEvents();
        initChat();
    });

})(window, document);