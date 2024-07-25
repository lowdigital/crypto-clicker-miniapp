document.addEventListener('contextmenu', event => event.preventDefault());

let userBalance = 0;
	
$(document).ready(() => {	
    const clickArea = document.getElementById('click-area');
    clickArea.addEventListener('touchmove', event => event.preventDefault());
	
    let coinClicks = 0;
    const user = Telegram.WebApp.initDataUnsafe.user;
    const clickSound = document.getElementById('click-sound');
    const screamSound = document.getElementById('scream-sound');
    let userTickets = 0;
    let gameStart = 0;

    let clickCounter = 0;
    let clickTimer = null;

    let isPaused = false;
    let itemSpeed = 13.33;

    let recordText = "";

    let fallGameRevenue = 1000;
	
	

    Telegram.WebApp.ready();
    Telegram.WebApp.expand();
    Telegram.WebApp.disableVerticalSwipes();

    const overflow = 100;
    const touchMoveHandler = event => {
        if (event.targetTouches.length === 1 || event.targetTouches.length > 1) {
            event.preventDefault();
        }
    };

    const enableScroll = () => {
        document.body.style.overflowY = 'scroll';
        document.body.style.height = 'auto';
        document.body.style.marginTop = '0px';
        document.body.style.paddingBottom = '0px';
        window.scrollTo(0, 0);

        document.removeEventListener('touchmove', touchMoveHandler, { passive: false });
    };

    const disableScroll = () => {
        document.body.style.overflowY = 'hidden';
        document.body.style.marginTop = `${overflow}px`;
        document.body.style.height = `${window.innerHeight + overflow}px`;
        document.body.style.paddingBottom = `${overflow}px`;
        window.scrollTo(0, overflow);

        document.addEventListener('touchmove', touchMoveHandler, { passive: false });
    };

    const checkAndToggleScroll = () => {
        const isLandscape = window.innerWidth > window.innerHeight;
        const contentHeight = document.body.scrollHeight;

        if (isLandscape || contentHeight > window.innerHeight) {
            enableScroll();
        } else {
            disableScroll();
        }
    };

    checkAndToggleScroll();
    window.addEventListener('resize', checkAndToggleScroll);

    const buttons = {
        leaders: document.querySelector('.btn-leaders'),
        main: document.querySelector('.btn-main'),
        friends: document.querySelector('.btn-friends'),
        about: document.querySelector('.btn-about'),
        boost: document.querySelector('.btn-boost')
    };

    const pages = {
        game: document.querySelector('.page-game'),
        leaders: document.querySelector('.page-leaders'),
        main: document.querySelector('.page-main'),
        friends: document.querySelector('.page-friends'),
        about: document.querySelector('.page-about'),
        boost: document.querySelector('.page-boost')
    };

	const showPage = (pageToShow) => {
		Object.values(pages).forEach(page => page.style.display = 'none');
		Object.values(buttons).forEach(button => button.classList.remove('mactive'));

		pageToShow.style.display = 'block';
		const activeButtonKey = Object.keys(pages).find(key => pages[key] === pageToShow);
		if (activeButtonKey && buttons[activeButtonKey]) {
			buttons[activeButtonKey].classList.add('mactive');
		}
	};

    buttons.leaders.addEventListener('click', () => {
        if (navigator.vibrate) navigator.vibrate(50);
        showPage(pages.leaders);
        enableScroll();
    });

    buttons.main.addEventListener('click', () => {
        if (navigator.vibrate) navigator.vibrate(50);
        showPage(pages.main);
        checkAndToggleScroll();
    });

    buttons.friends.addEventListener('click', () => {
        if (navigator.vibrate) navigator.vibrate(50);
        showPage(pages.friends);
        enableScroll();
    });

    buttons.about.addEventListener('click', () => {
        if (navigator.vibrate) navigator.vibrate(50);
        showPage(pages.about);
        enableScroll();
    });

    buttons.boost.addEventListener('click', () => {
        if (navigator.vibrate) navigator.vibrate(50);
        showPage(pages.boost);
        enableScroll();
        $.post('/api/interface/update.php', { user_id: user.id, user_name: user.username }, (response) => {
            updateUI(response);
            loadBoosters();
        });
    });

	const getTicket = () => {
		if (navigator.vibrate) navigator.vibrate(50);
		const giftButton = $('.card-gift');
		giftButton.prop('disabled', true);

		$.post('/api/minigame/get_ticket.php', { user_id: user.id })
			.done((response) => {
				let json;
				try {
					json = JSON.parse(response);
				} catch (e) {
					Swal.fire({
						icon: "error",
						title: "Ошибка",
						html: "Некорректный ответ от сервера. Попробуйте позже.",
						confirmButtonText: "Закрыть",
						confirmButtonColor: "#db4552",
						allowOutsideClick: false
					}).then(() => {
						if (navigator.vibrate) navigator.vibrate(50);
						giftButton.prop('disabled', false);
					});
					return;
				}

				if (json.status === 'ok') {
					Swal.fire({
						icon: "success",
						title: "Отлично!",
						html: json.text,
						confirmButtonText: "Забрать билеты",
						confirmButtonColor: "#8bc05d",
						allowOutsideClick: false
					}).then(() => {
						const giftSound = document.getElementById('gift-sound');
						giftSound.play();
						if (navigator.vibrate) navigator.vibrate(50);

						rainbowFireworks(window.innerWidth / 2, window.innerHeight / 2, 'red');

						$.post('/api/interface/update.php', { user_id: user.id, user_name: user.username }, (response) => {
							updateUI(response);
							giftButton.prop('disabled', false);
						});
					});
				} else {
					Swal.fire({
						icon: "error",
						title: "Ошибка",
						html: "Сейчас нельзя получить бесплатные билеты. Вернитесь чуть позже",
						confirmButtonText: "Закрыть",
						confirmButtonColor: "#db4552",
						allowOutsideClick: false
					}).then(() => {
						if (navigator.vibrate) navigator.vibrate(50);
						giftButton.prop('disabled', false);
					});
				}
			})
			.fail(() => {
				Swal.fire({
					icon: "error",
					title: "Ошибка",
					html: "Не удалось выполнить запрос. Попробуйте позже.",
					confirmButtonText: "Закрыть",
					confirmButtonColor: "#db4552",
					allowOutsideClick: false
				}).then(() => {
					if (navigator.vibrate) navigator.vibrate(50);
					giftButton.prop('disabled', false);
				});
			});
	};

	$('.card-gift').on('click', getTicket);

	const updateClickAreaBackground = () => {
		if (userBalance >= 100000000) {
			clickArea.style.backgroundImage = "url('/assets/img/main_coin_3.png')";
		} else if (userBalance >= 10000000) {
			clickArea.style.backgroundImage = "url('/assets/img/main_coin_2.png')";
		} else {
			clickArea.style.backgroundImage = "url('/assets/img/main_coin_1.png')";
		}
	};

    let tapIncome = 10;

    const updateUI = (response) => {
        const json = jQuery.parseJSON(response);
        tapIncome = parseInt(json.tap_income);
        fallGameRevenue = parseInt(json.game_revenue);

        userBalance = parseInt(json.balance);
        const formattedBalance = userBalance.toLocaleString('ru-RU');
        $("#balance_num").html(formattedBalance);
        $("#ticket_num").html(json.tickets);
        userTickets = json.tickets;
        const ticketGift = json.gift;
        const giftRemaining = json.gift_remaining;

        if (userTickets > 0) {
            $(".card-game").addClass("bg-red-dark").removeClass("bg-dark-light");
        } else {
            $(".card-game").addClass("bg-dark-light").removeClass("bg-red-dark");
        }

        if (ticketGift || giftRemaining <= 0) {
            $(".card-gift").addClass("bg-green-dark");
            $(".gift-remaining").text("Получить");
        } else {
            $(".card-gift").removeClass("bg-green-dark");
            startGiftTimer(giftRemaining);
        }

        const leaderboardList = $("#leaderboard-list");
        leaderboardList.empty();

        const leaders = json.leaders.slice(0, 5);
        leaders.forEach(leader => {
            const formattedLeaderBalance = parseInt(leader.balance).toLocaleString('ru-RU');
            const listItem = `
                <div style="width: 100%; border-radius: 10px; background: #4a89dc; padding: 10px; margin-bottom: 10px;">
                    <div class="row" style="margin:0; padding:0;">
                        <div class="col-4" style="margin:0; padding:0; padding-left: 8px;">
                            <img src="${leader.avatar}" style="width: 100%; border-radius:50%; max-width: 150px;">
                        </div>
                        <div class="col-8" style="margin:0; padding:0">
                            <div class="content">
                                <p class="mb-n1 color-white opacity-50 font-600">${formattedLeaderBalance} BMJ</p>
                                <h2 class="color-white">${leader.name}</h2>
                            </div>
                        </div>
                    </div>
                </div>`;
            leaderboardList.append(listItem);
        });
		
		updateClickAreaBackground();
    };

    let giftTimerInterval;
    const startGiftTimer = (seconds) => {
        const giftRemainingElement = document.querySelector('.gift-remaining');

        if (giftTimerInterval) {
            clearInterval(giftTimerInterval);
        }

        giftTimerInterval = setInterval(() => {
            if (seconds <= 0) {
                clearInterval(giftTimerInterval);
                giftRemainingElement.textContent = 'Получить';
                $(".card-gift").addClass("bg-green-dark");
            } else {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const remainingSeconds = seconds % 60;
                giftRemainingElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
                seconds--;
            }
        }, 1000);
    };

    $.post('/api/interface/update.php', { user_id: user.id, user_name: user.username }, (response) => {
        updateUI(response);
    });

    const handleTouch = (touch) => {
        if (clickCounter >= 20) return;

        clickCounter++;

        if (clickTimer === null) {
            clickTimer = setTimeout(() => {
                clickCounter = 0;
                clickTimer = null;
            }, 1000);
        }

        const circle = document.createElement('div');
        circle.classList.add('ripple');
        circle.style.width = circle.style.height = `${Math.max(clickArea.clientWidth, clickArea.clientHeight)}px`;
        circle.style.left = `${touch.clientX - clickArea.offsetLeft - circle.offsetWidth / 2}px`;
        circle.style.top = `${touch.clientY - clickArea.offsetTop - circle.offsetHeight / 2}px`;

        clickArea.appendChild(circle);

        setTimeout(() => circle.remove(), 600);

        if (navigator.vibrate) navigator.vibrate(50);

        coinClicks++;
        if (coinClicks == 500) {
            coinClicks = 0;
            screamSound.play();
        } else {
            clickSound.play();
        }

        const plusTen = document.createElement('div');
        plusTen.classList.add('float-up');
        plusTen.innerText = `+${tapIncome}`;

        const content = clickArea.closest('.content');
        const rect = content.getBoundingClientRect();
        const offsetX = touch.clientX - rect.left;
        const offsetY = touch.clientY - rect.top;

        plusTen.style.left = `${offsetX}px`;
        plusTen.style.top = `${offsetY}px`;

        content.appendChild(plusTen);

        setTimeout(() => plusTen.remove(), 1000);

        $.post('/api/interface/update.php', { user_id: user.id, user_name: user.username }, (response) => {
            updateUI(response);
        });
    };

    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    if (isMobile) {
        clickArea.addEventListener('touchstart', event => {
            for (let i = 0; i < event.touches.length; i++) {
                handleTouch(event.touches[i]);
            }
        });
    } else {
        clickArea.addEventListener('click', event => handleTouch(event));
    }

	const handleTicketsClick = () => {
		if (navigator.vibrate) navigator.vibrate(50);
		const ticketsButton = $('.card-game .content');
		ticketsButton.prop('disabled', true);

		if (userTickets > 0) {
			$.post('/api/minigame/use_ticket.php', { user_id: user.id }, (response) => {
				if (response === "ok") {
					$("#footer-bar").css("display", "none");

					showPage(pages.game);
					startGame();
				} else {
					Swal.fire({
						icon: "error",
						title: "Ошибка",
						html: response,
						confirmButtonText: "Закрыть",
						confirmButtonColor: "#db4552",
						allowOutsideClick: false
					}).then(() => {
						if (navigator.vibrate) navigator.vibrate(50);

						$.post('/api/interface/update.php', { user_id: user.id, user_name: user.username }, (response) => {
							updateUI(response);
							ticketsButton.prop('disabled', false);
						});
					});
				}
			}).fail(() => {
				ticketsButton.prop('disabled', false);
			});
		} else {
			Swal.fire({
				icon: "error",
				title: "Упс...",
				html: "У вас нет билетов для игры в \"Поймай мерзавчик\". Приходите завтра.",
				confirmButtonText: "Закрыть",
				confirmButtonColor: "#db4552",
				allowOutsideClick: false
			}).then(() => {
				if (navigator.vibrate) navigator.vibrate(50);
				ticketsButton.prop('disabled', false);
			});
		}
	};
	
	$('.card-game .content').on('click', handleTicketsClick);

    $.post('/api/interface/affilate.php', { user_id: user.id }, (response) => {
        const data = JSON.parse(response);
        if (!data.ref_str) return;

        $('#referral-link-text').text(`https://t.me/bmjcoinbot?start=${data.ref_str}`);
        $('#referral-income').text(`${parseInt(data.referal_income).toLocaleString('ru-RU')} BMJ`);

        const referralList = $('#referral-list');
        referralList.empty();

        if (data.referals.length > 0) {
            data.referals.forEach(ref => {
                const listItem = `
                    <div class="card card-style bg-blue-dark">
                        <div class="content">
                            <p class="mb-n1 color-white opacity-30 font-600">${ref.date}</p>
                            <div class="mt-n2 font-30 color-yellow-dark float-end me-2 scale-box">
                                <img src="${ref.avatar}" style="width:32px; border-radius:50%;">
                            </div>
                            <h1 class="color-white">${ref.name}</h1>
                            <p class="color-white">
                                Баланс друга: <strong>${parseInt(ref.balance).toLocaleString('ru-RU')} BMJ</strong><br>
                                Доход друга за сутки: <strong>${parseInt(ref.daily_income_f).toLocaleString('ru-RU')} BMJ</strong><br>
                                Твой доход за сутки: <strong>${parseInt(ref.daily_income).toLocaleString('ru-RU')} BMJ</strong>
                            </p>
                        </div>
                    </div>`;
                referralList.append(listItem);
            });
        } else {
            referralList.html('<p>У тебя пока нет друзей, пригласи их с помощью своей реферальной ссылки, чтобы начать зарабатывать!</p>');
        }
    });

    const copyReferralLink = () => {
        if (navigator.vibrate) navigator.vibrate(50);

        const referralLink = document.getElementById('referral-link-text').textContent;
        const textToCopy = `${referralLink}\n\nИграй со мной! Стань самым крутым бомжом в крипто-тусовке и получи токены через аирдроп!\n\n💸  Бонус за друга 1 000 000 BMJ-монет в качестве первого подарка`;

        copyTextToClipboard(textToCopy);
    };
	
	$('.copy-btn, .referral-link').on('click', copyReferralLink);

    const createFallingCoins = () => {
        const coinContainer = document.createElement('div');
        coinContainer.style.position = 'fixed';
        coinContainer.style.top = '0';
        coinContainer.style.left = '0';
        coinContainer.style.width = '100%';
        coinContainer.style.height = '100%';
        coinContainer.style.pointerEvents = 'none';
        coinContainer.style.zIndex = '10000';
        document.body.appendChild(coinContainer);

        const numberOfCoins = 50;

        for (let i = 0; i < numberOfCoins; i++) {
            const coin = document.createElement('div');
            coin.classList.add('coin');
            coin.style.left = `${Math.random() * 100}%`;
            coin.style.animationDelay = `${Math.random() * 0.5}s`;
            coinContainer.appendChild(coin);
        }

        setTimeout(() => {
            document.body.removeChild(coinContainer);
        }, 1000);
    };

    const gameArea = document.getElementById('game-area');
    const scoreElement = document.getElementById('score');
    const timeElement = document.getElementById('time');
    const overlay = document.getElementById('overlay');
    const freezeOverlay = document.getElementById('freeze-overlay');
    const scoreboard = document.querySelector('.scoreboard');

    const glassBreakSound = document.getElementById('glass-break-sound');
    const clocksSound = document.getElementById('clocks-sound');
    const bombSound = document.getElementById('bomb-sound');
    const coinsSound = document.getElementById('coins-sound');

    let score = 0;
    let time = 30;
    let gameInterval;
    let fallingItemsInterval;
    let isFrozen = false;
    let activeItems = [];

    const itemTypes = [
        ...Array(50).fill({ src: '/assets/img/minigame_bottle.png', type: 'score', points: 1, enlarged: true }),
        ...Array(2).fill({ src: '/assets/img/minigame_freeze.png', type: 'freeze', enlarged: true }),
        ...Array(20).fill({ src: '/assets/img/minigame_bomb.png', type: 'bomb', enlarged: false })
    ];

    let ticketsButton = $('.card-game .content');

    const startGame = () => {
        gameStart = 1;
        clearFallingItems();

        score = 0;
        time = 30;
        isFrozen = false;

        if (gameInterval) clearInterval(gameInterval);
        if (fallingItemsInterval) clearInterval(fallingItemsInterval);

        gameInterval = setInterval(updateTime, 1000);
        fallingItemsInterval = setInterval(createFallingItem, 250);
    };

    const endGame = () => {
        ticketsButton.prop('disabled', false);

        if (gameStart == 1) {
            gameStart = 0;
            clearInterval(gameInterval);
            clearInterval(fallingItemsInterval);
            activeItems.forEach(item => clearInterval(item.fallingInterval));
            activeItems = [];

            const bmjEarned = (score * fallGameRevenue).toLocaleString('ru-RU');

            $("#footer-bar").css("display", "flex");

            showPage(pages.main);

            if (navigator.vibrate) navigator.vibrate(50);

            recordText = `https://t.me/bmjcoinbot\nУ меня ${score} очков в "Поймай мерзавчик"! Сможешь лучше?`;

            Swal.fire({
                icon: "success",
                title: "Отлично!",
                html: `Собрано фунфыриков: ${score}<br>Вы заработали ${bmjEarned} BMJ`,
                confirmButtonText: "Забрать награду",
                footer: '<a href="#" class="share-record">поделиться рекордом</a>',
                confirmButtonColor: "#8bc05d",
                allowOutsideClick: false
            }).then(() => {
                if (navigator.vibrate) navigator.vibrate(50);
                coinsSound.play();
                createFallingCoins();

                $.post('/api/interface/update.php', { user_id: user.id, user_name: user.username }, (response) => {
                    updateUI(response);
                });
            });
        }
    };

    const clearFallingItems = () => {
        while (gameArea.firstChild) {
            gameArea.removeChild(gameArea.firstChild);
        }
        activeItems = [];
        activeItems.forEach(item => clearInterval(item.fallingInterval));
        activeItems = [];
    };

    const copyTextToClipboard = (text) => {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.opacity = '0';
        textArea.style.zIndex = '-1';
        document.body.appendChild(textArea);

        textArea.select();
        try {
            document.execCommand('copy');
        } catch (err) {}

        document.body.removeChild(textArea);
    };

    $("body").on("click", ".share-record", event => {
        event.preventDefault();
        if (navigator.vibrate) navigator.vibrate(50);
        copyTextToClipboard(recordText);
    });

    const updateTime = () => {
        if (isPaused) return;
        if (time <= 0) {
            endGame();
        } else {
            time--;
            timeElement.innerText = time;
        }
    };

    const createFallingItem = () => {
        if (isPaused) return;

        const item = document.createElement('img');
        const randomType = itemTypes[Math.floor(Math.random() * itemTypes.length)];

        item.src = randomType.src;
        item.classList.add('item');
        item.dataset.type = randomType.type;
        if (randomType.points) {
            item.dataset.points = randomType.points;
        }
        if (randomType.enlarged) {
            item.classList.add('enlarged');
        }

        item.style.top = '0px';
        item.style.left = `${Math.random() * (window.innerWidth - 75)}px`;
        item.style.transform = `rotate(${Math.random() * 360}deg)`;

        item.addEventListener('mousedown', event => handleItemClick(item));
        item.addEventListener('touchstart', event => handleItemClick(item));

        gameArea.appendChild(item);

        const fall = () => {
            if (!isPaused) {
                if (parseInt(item.style.top) > window.innerHeight - 75) {
                    item.remove();
                    clearInterval(item.fallingInterval);
                    activeItems = activeItems.filter(i => i !== item);
                } else {
                    item.style.top = `${parseInt(item.style.top) + itemSpeed}px`;
                }
            }
        };

        item.fallingInterval = setInterval(fall, 50);
        activeItems.push(item);
    };

    const playSound = (sound) => {
        const clone = sound.cloneNode();
        clone.play();
    };

    const handleItemClick = (item) => {
        if (navigator.vibrate) navigator.vibrate(50);

        if (item.dataset.type === 'score') {
            score += parseInt(item.dataset.points);
            scoreElement.innerText = score;

            playSound(glassBreakSound);
            launchFireworks(item, 'green');
            flashGreenScreen();

            $.post('/api/minigame/catch.php', { user_id: Telegram.WebApp.initDataUnsafe.user.id }, () => {});
        } else if (item.dataset.type === 'freeze') {
            isPaused = true;
            scoreboard.classList.add('pulse-bg');
            clearInterval(fallingItemsInterval);
            clearInterval(gameInterval);
            stopAllItems();

            playSound(clocksSound);
            launchFireworks(item, 'blue');
            freezeOverlay.style.display = 'block';

            setTimeout(() => {
                if (isPaused) {
                    isPaused = false;
                    freezeOverlay.style.display = 'none';
                    scoreboard.classList.remove('pulse-bg');
                    resumeAllItems();
                    fallingItemsInterval = setInterval(createFallingItem, 250);
                    gameInterval = setInterval(updateTime, 1000);
                }
            }, 3000);
        } else if (item.dataset.type === 'bomb') {
            time -= 5;
            if (time < 0) time = 0;
            timeElement.innerText = time;

            playSound(bombSound);
            launchFireworks(item, 'black');
            flashRedScreen();
        }
        item.remove();
    };

    const flashRedScreen = () => {
        scoreboard.classList.add('flash-red');
        setTimeout(() => {
            scoreboard.classList.remove('flash-red');
        }, 500);
    };

    const flashGreenScreen = () => {
        scoreboard.classList.add('flash-green');
        setTimeout(() => {
            scoreboard.classList.remove('flash-green');
        }, 500);
    };

    const stopAllItems = () => {
        isPaused = true;
    };

    const resumeAllItems = () => {
        isPaused = false;
    };

    const launchFireworks = (item, color) => {
        const canvas = document.createElement('canvas');
        canvas.id = 'fireworksCanvas';
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '9999';
        document.body.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        const particleCount = 30;
        const colors = {
            'green': '#00ff00',
            'blue': '#0000ff',
            'black': '#000000',
			'red': '#ff0000',
        };

        const createParticle = (x, y) => {
            const angle = Math.random() * 2 * Math.PI;
            const speed = Math.random() * 5 + 2;
            return {
                x,
                y,
                dx: Math.cos(angle) * speed,
                dy: Math.sin(angle) * speed,
                color: colors[color],
                life: 50
            };
        };

        for (let i = 0; i < particleCount; i++) {
            particles.push(createParticle(parseInt(item.style.left) + 25, parseInt(item.style.top) + 25));
        }

        const updateParticles = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach((particle, index) => {
                particle.x += particle.dx;
                particle.y += particle.dy;
                particle.life -= 1;

                if (particle.life <= 0) {
                    particles.splice(index, 1);
                }

                ctx.fillStyle = particle.color;
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, 3, 0, 2 * Math.PI);
                ctx.fill();
            });

            if (particles.length > 0) {
                requestAnimationFrame(updateParticles);
            } else {
                document.body.removeChild(canvas);
            }
        };

        updateParticles();
    };
	
	const rainbowFireworks = (x, y) => {
		const canvas = document.createElement('canvas');
		canvas.id = 'fireworksCanvas';
		canvas.style.position = 'fixed';
		canvas.style.top = '0';
		canvas.style.left = '0';
		canvas.style.width = '100%';
		canvas.style.height = '100%';
		canvas.style.pointerEvents = 'none';
		canvas.style.zIndex = '9999';
		document.body.appendChild(canvas);

		const ctx = canvas.getContext('2d');
		canvas.width = window.innerWidth;
		canvas.height = window.innerHeight;

		const particles = [];
		const particleCount = 100;
		const colors = ['#00ff00', '#0000ff', '#000000', '#ff0000', '#ffff00', '#00ffff', '#ff00ff'];

		const createParticle = (x, y) => {
			const angle = Math.random() * 2 * Math.PI;
			const speed = Math.random() * 5 + 2;
			const color = colors[Math.floor(Math.random() * colors.length)];
			return {
				x,
				y,
				dx: Math.cos(angle) * speed,
				dy: Math.sin(angle) * speed,
				color,
				life: 100
			};
		};

		for (let i = 0; i < particleCount; i++) {
			particles.push(createParticle(x, y));
		}

		const updateParticles = () => {
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			particles.forEach((particle, index) => {
				particle.x += particle.dx;
				particle.y += particle.dy;
				particle.life -= 1;

				if (particle.life <= 0) {
					particles.splice(index, 1);
				}

				ctx.fillStyle = particle.color;
				ctx.beginPath();
				ctx.arc(particle.x, particle.y, 3, 0, 2 * Math.PI);
				ctx.fill();
			});

			if (particles.length > 0) {
				requestAnimationFrame(updateParticles);
			} else {
				document.body.removeChild(canvas);
			}
		};

		updateParticles();
	};

	const loadBoosters = () => {
		$.post('/api/boost/list.php', { user_id: user.id, user_name: user.username }, (response) => {
			const boosters = response;
			const boosterContainer = $('.page-boost .row');
			boosterContainer.empty();

			if (boosters.length === 0) {
				boosterContainer.append('<div class="col-12"><p class="text-center">Нет доступных улучшений.</p></div>');
				return;
			}

			boosters.forEach(booster => {
				const boosterLvl = booster.booster_lvl;
				const maxLvl = booster.booster_max_lvl;
				const nextLvl = boosterLvl + 1;
				const upgradePrice = booster.booster_prices[nextLvl] || 'Максимум';

				const priceForNextLvl = booster.booster_prices[nextLvl];
				const canAfford = typeof priceForNextLvl === 'number' && userBalance >= priceForNextLvl;

				const currencyIcon = nextLvl > maxLvl ? '' : canAfford
					? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
						   <circle cx="12" cy="12" r="10" fill="#FFD700" stroke="#DAA520" stroke-width="4"/>
					   </svg>`
					: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
						   <circle cx="12" cy="12" r="10" fill="#C0C0C0" stroke="#A9A9A9" stroke-width="4"/>
					   </svg>`;

				const description = booster.booster_descriptions && booster.booster_descriptions[nextLvl]
					? booster.booster_descriptions[nextLvl]
					: booster.booster_description;

				const boosterCard = `
					<div class="col-6" style="padding: 0;">
						<a href="#" class="booster-card" data-id="${booster.booster_code}" data-img="${booster.booster_image}" data-title="${booster.booster_name} (lvl ${nextLvl})" data-description="${description}" data-price="${upgradePrice}" data-lvl="${boosterLvl}" data-max-lvl="${maxLvl}">
							<div class="card card-style shadow-xl">
								<div class="content">
									<center>
										<img src="${booster.booster_image}" style="border-radius: 10px; width: 100%">
										<b>${typeof upgradePrice === 'number' ? upgradePrice.toLocaleString('ru-RU') : upgradePrice}</b>
										${currencyIcon}
										<br>
										${booster.booster_name}<br>
										<span style="color: gray;">(уровень: ${boosterLvl})</span>
									</center>
								</div>
							</div>
						</a>
					</div>`;
				boosterContainer.append(boosterCard);
			});
		}).fail((xhr, status, error) => {
			console.error("Ошибка при загрузке улучшений:", error);
		});
	};

	$.post('/api/interface/update.php', { user_id: user.id, user_name: user.username }, (response) => {
		const json = jQuery.parseJSON(response);
		userBalance = parseInt(json.balance);
		updateUI(response);
		loadBoosters();
	});

    $(document).on('click', '.booster-card', function (e) {
        e.preventDefault();
        const boosterImg = $(this).data('img');
        const boosterTitle = $(this).data('title');
        const boosterDescription = $(this).data('description');
        const boosterPrice = $(this).data('price');
        const boosterLvl = $(this).data('lvl');
        const maxLvl = $(this).data('max-lvl');

        if (boosterLvl >= maxLvl) {
            Swal.fire({
                icon: 'error',
                title: 'Максимальный уровень',
                text: 'Вы достигли максимального уровня улучшения.',
                confirmButtonColor: "#db4552",
                confirmButtonText: 'Закрыть'
            });
            return;
        }

        Swal.fire({
            title: boosterTitle,
            html: `
                <img src="${boosterImg}" style="border-radius: 10px; width: 50%; margin-bottom: 20px;">
                <p>${boosterDescription}</p>
                <p><strong>Стоимость улучшения:</strong> ${typeof boosterPrice === 'number' ? boosterPrice.toLocaleString('ru-RU') : boosterPrice} BMJ</p>
            `,
            showCancelButton: true,
            confirmButtonText: 'Купить',
            confirmButtonColor: "#8bc05d",
            cancelButtonText: 'Отмена',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                buyBooster($(this).data('id'), boosterLvl + 1);
            }
        });
    });

    const buyBooster = (boosterCode, newLevel) => {
        $.post('/api/boost/buy.php', { user_id: user.id, booster_code: boosterCode, booster_level: newLevel }, (response) => {
            const data = response;
            if (data.status === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Покупка завершена',
                    text: 'Вы получили новое улучшение!',
                    confirmButtonColor: "#8bc05d",
                    confirmButtonText: 'Закрыть'
                }).then(() => {
					$.post('/api/interface/update.php', { user_id: user.id, user_name: user.username }, (response) => {
						const json = jQuery.parseJSON(response);
						userBalance = parseInt(json.balance); // Убедитесь, что переменная определена
						updateUI(response);
						loadBoosters();
					});
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: data.message,
                    confirmButtonColor: "#db4552",
                    confirmButtonText: 'Закрыть'
                });
            }
        }).fail((xhr, status, error) => {
            console.error("Ошибка при покупке бустера:", error);
        });
    };
});
