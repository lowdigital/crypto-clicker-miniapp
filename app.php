<?php
    const VERSION = 400;
?><!DOCTYPE HTML>
<html lang="ru">
<head>
    <title>BMJ COIN</title>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="/core/styles/bootstrap.css">
    <link rel="stylesheet" href="/core/styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/core/fonts/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="/assets/sweetalert2/sweetalert2.css">
    <link rel="stylesheet" href="/app.css?<?= VERSION; ?>">
</head>
<body class="theme-light" data-highlight="highlight-red">
    <div id="preloader">
        <div class="spinner-border color-highlight" role="status"></div>
    </div>

    <div id="page" style="margin-top: -10px;">
        <div id="footer-bar" class="footer-bar-6">
            <a href="#" class="btn-leaders">
                <i class="fa fa-trophy"></i>
                <span>Лидеры</span>
            </a>
            <a href="#" class="btn-boost">
                <i class="fa fa-puzzle-piece"></i>
                <span>Улучшения</span>
            </a>
            <a href="#" class="btn-main circle-nav active-nav mactive">
                <i class="fa fa-coins"></i>
                <span>Майнить</span>
            </a>
            <a href="#" class="btn-friends">
                <i class="fa fa-user-friends"></i>
                <span>Друзья</span>
            </a>
            <a href="#" class="btn-about">
                <i class="fa fa-info-circle"></i>
                <span>О проекте</span>
            </a>
        </div>
        <div class="page-content">
            <br>
            <div class="page-game" style="display: none;">
                <div class="scoreboard">
                    <b>Фунфырики:</b>
                    <span id="score">0</span>
                    <br>
                    <b>Время:</b>
                    <span id="time">30</span> сек.
                </div>
                <div id="game-area">
                    <div class="overlay" id="overlay"></div>
                    <div class="freeze-overlay" id="freeze-overlay"></div>
                </div>
                <audio id="glass-break-sound" src="/assets/audio/minigame_glass.mp3"></audio>
                <audio id="clocks-sound" src="/assets/audio/minigame_clock.mp3"></audio>
                <audio id="bomb-sound" src="/assets/audio/minigame_bomb.mp3"></audio>
            </div>

            <div class="page-leaders" style="display: none;">
                <div class="card card-style shadow-xl">
                    <div class="content">
                        <p class="color-highlight font-600 mb-n1">ТОП-5 лучших</p>
                        <h1 class="font-24 font-700 mb-2">Лидеры
                            <i class="fa fa-star mt-n2 font-30 color-yellow-dark float-end me-2 scale-box"></i>
                        </h1>
                        <div id="leaderboard-list" style="margin:0; padding:0; width: 100%; margin-bottom:-10px;"></div>
                    </div>
                </div>
            </div>

            <div class="page-main">
                <div class="content card card-style shadow-xl bg-dark-light" style="margin-bottom: 10px;">
                    <div class="">
                        <div class="container">
                            <center>
                                <div id="balance" style="font-size:14px; margin-bottom: 10px; padding-top: 16px;">БАЛАНС <br>
                                    <span id="balance_num" style="font-weight: 600; font-size:24px;">0</span> BMJ
                                </div>
                                <div id="click-area" class="click-area"></div>
                            </center>
                        </div>
                    </div>
                </div>
                <div class="card-game card card-style shadow-xl" style="margin-bottom: 10px;">
                    <div class="content">
                        <div class="row" style="margin: 0; cursor: pointer;">
                            <div class="col-9">Поймай мерзавчик</div>
                            <div class="col-3" style="text-align: right;">
								<i class="fa fa-ticket" style="color: white"></i> <span id="ticket_num">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-gift card card-style shadow-xl" style="margin-bottom: 10px;">
                    <div class="content" onclick="getTicket()">
                        <div class="row" style="margin: 0; cursor: pointer;">
                            <div class="col-8">Получить билеты</div>
                            <div class="col-4 gift-remaining" style="text-align: right;">00:00:00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="myModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <p>У вас не хватает билетов, приходите завтра</p>
                    </div>
                </div>
                <audio id="click-sound" src="/assets/audio/main_mine.mp3"></audio>
                <audio id="scream-sound" src="/assets/audio/main_scream.mp3"></audio>
            </div>

            <div class="page-friends" style="display: none;">
                <div class="card card-style shadow-xl">
                    <div class="content">
                        <p class="color-highlight font-600 mb-n1">Пусть работают другие</p>
                        <h1 class="font-24 font-700 mb-2">Реферальная <br>программа
                            <button class="copy-btn mt-n2 font-30 color-yellow-dark float-end me-2 scale-box">
                                <img src="https://img.icons8.com/ios-glyphs/30/4CAF50/copy.png" alt="Copy">
                            </button>
                        </h1>
                        <p>Привлекай друзей и ежедневно получай 15% от их заработка в BMJ Coin. Чем больше друзей присоединяется к нашей платформе через твою ссылку, тем выше твой ежедневный доход. Убедись, что они используют твою реферальную ссылку при регистрации.</p>
                        <div class="card card-style bg-green-light">
                            <div class="content">
                                <p class="mb-n1 font-600 color-white">Твоя уникальная реферальная ссылка</p>
                                <div class="referral-link" id="referral-link-text" style="cursor: pointer; background: white; border-radius: 10px; padding: 10px; font-size: 18px; font-weight: 600; color: #69c3e0;" onclick="copyReferralLink()"></div>
                                <p class="opacity-60 color-white"> По мимо ежедневного дохода, за каждого привлеченного друга ты сразу получишь по 2 000 000 BMJ, а твой друг - 1 000 000 BMJ </p>
                            </div>
                        </div>
                        <p>Также, за каждого приведенного друга ты получаешь дополнительно 5 билетов для игры в "Поймай мерзавчик"</p>
                    </div>
                </div>
                <div class="card card-style bg-dark-light ms-0 me-0 rounded-0">
                    <div class="content">
                        <h1 class="color-white">Твой доход за сутки</h1>
                        <p class="color-white opacity-60">
                            <strong id="referral-income" style="font-size: 28px;">0 BMJ</strong>
                        </p>
                    </div>
                </div>
                <div class="card card-style shadow-xl">
                    <div class="content">
                        <p class="color-highlight font-600 mb-n1">Уже работают на тебя</p>
                        <h1 class="font-24 font-700 mb-2">Друзья</h1>
                        <div class="referral-list" id="referral-list">
                            <p>Загрузка друзей...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-about" style="display: none;">
                <div class="card card-style shadow-xl">
                    <div class="content">
                        <p class="color-highlight font-600 mb-n1">Бомженомика</p>
                        <h1 class="font-24 font-700 mb-2">О проекте <i class="fa fa-info-circle mt-n2 font-30 color-yellow-dark float-end me-2 scale-box"></i>
                        </h1>
						<p class="mb-1">Автор проекта: <a href="https://t.me/low_digital" target="_blank">digital на минималках</a>. Игра началась как эксперимент, показанный в прямом эфире для демонстрации возможностей блокчейна TON. С момента своего зарождения проект быстро привлек внимание сообщества и стал активно развиваться.</p>
						<p class="mb-1">BOMJ COIN - это уникальная игра, в которой каждый пользователь может окунуться в мир виртуальных возможностей и стать настоящим криптовалютным магнатом. В начале пути вы начинаете как простой бомж, но с каждой заработанной монетой приближаетесь к вершине славы и богатства. Проект предлагает множество увлекательных активностей: ловите мерзавчиков, чтобы зарабатывать монеты, улучшайте свои навыки и оборудование, получайте бонусы и участвуйте в игровых событиях. Каждое ваше действие в игре приближает вас к новым уровням и достижениями. Здесь вы можете испытать дух соревнования, взлететь в лидеры и доказать всем свою уникальность.</p>
						<p class="mb-1">Следите за обновлениями и новостями проекта в Telegram-канале: <a href="https://t.me/low_digital" target="_blank">digital на минималках</a>. Узнавайте первыми о новых функциях, акциях и событиях, а также делитесь своими успехами и впечатлениями с другими участниками сообщества.</p>
                    </div>
                </div>
            </div>

            <div class="page-boost" style="display: none;">
                <div class="row" style="padding-left: 10px; padding-right: 10px;">
                </div>
            </div>
        </div>
    </div>

    <audio id="coins-sound" src="/assets/audio/minigame_win.mp3"></audio>
    <audio id="gift-sound" src="/assets/audio/main_tickets.mp3"></audio>

    <script src="/core/scripts/bootstrap.min.js"></script>
    <script src="/core/scripts/custom.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="/assets/sweetalert2/sweetalert2.js"></script>
    <script src="/app.js?<?= VERSION; ?>"></script>
</body>
</html>