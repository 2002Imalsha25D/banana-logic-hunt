<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Banana Logic Hunt</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
  <style>
    :root{
      --life-size: 50px;
      --board-safe-w: 64%;
      --board-safe-h: 72%;
      --board-safe-top: 50%;
      --board-safe-left: 50%;
      --answers-col-w: 120px;
      --answers-gap-x: 24px;
      --answers-gap-y: 18px;
    }

    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{font-family:'Poppins',system-ui,Arial;color:#fff;overflow-x:hidden}

    .page{
      min-height:100vh; position:relative;
      background:url("images/Game screen.png") center/cover no-repeat fixed;
    }
    .page::after{content:""; position:absolute; inset:0; background:rgba(0,0,0,.16)}

    .nav{
      position:relative; z-index:3;
      display:grid; grid-template-columns:1fr auto 1fr; align-items:center;
      padding:12px 20px; background:rgba(247, 191, 89, 0.22); backdrop-filter:blur(2px);
    }
    .lives{display:flex; gap:4px; align-items:center}
    .life{width:var(--life-size); height:var(--life-size); object-fit:contain}
    .score{text-align:center; font-weight:900; font-size:clamp(1.1rem,2.3vw,1.7rem)}
    .user-actions{display:flex; gap:12px; align-items:center; justify-self:end}
    .username{font-weight:800}
    .logout{
      border:0; cursor:pointer; color:#fff; background:#4b5a2c;
      font-weight:900; padding:10px 16px; border-radius:12px;
      box-shadow:0 2px 6px rgba(0,0,0,.28);
      transition:transform .12s ease, filter .12s ease;
    }
    .logout:hover{transform:translateY(-1px);filter:brightness(1.05)}

    /* right side levels icon */
    .levels-icon{
      position:absolute; z-index:3; top:74px; right:22px;
      width:66px; height:66px; cursor:pointer;
      transition:transform .15s ease, filter .15s ease;
    }
    .levels-icon:hover{transform:scale(1.08); filter:brightness(1.06)}

    /* left side mini-game icon */
    .mini-icon{
      position:absolute; z-index:3; top:74px; left:22px;
      width:66px; height:66px; cursor:pointer;
      transition:transform .15s ease, filter .15s ease;
    }
    .mini-icon:hover{transform:scale(1.08); filter:brightness(1.06)}

    .wrap{
      position:relative; z-index:2;
      display:grid; grid-template-columns:minmax(460px,520px) 1fr;
      gap:22px; padding:16px 22px 100px;
    }

    .board{
      position:relative; width:100%; min-height:640px;
      background:url("images/Answer board.png") center/contain no-repeat;
    }
    .board-inner{
      position:absolute;
      left:var(--board-safe-left); top:var(--board-safe-top);
      width:var(--board-safe-w); height:var(--board-safe-h);
      transform:translate(-50%,-50%);
      display:grid; grid-template-rows:auto 1fr auto; align-items:start;
    }

    .level-tag{
      justify-self:start; margin:0;
      background:rgba(255,255,255,.46); color:#3b2f09; font-weight:900;
      padding:6px 14px; border-radius:12px;
      margin-top:0px;
      margin-left:115px;
    }
    .question{
      color:#fff; text-shadow:0 2px 10px rgba(0,0,0,.38);
      font-size:clamp(1.35rem,2.3vw,1.7rem);
      line-height:1.4; max-width:80%;
      margin-top:80px; margin-left:80px;
      white-space:normal; text-align:left;
    }
    .answers{
      align-self:end;
      display:grid;
      grid-template-columns:repeat(2, var(--answers-col-w));
      gap:var(--answers-gap-y) var(--answers-gap-x);
      justify-content:start;
    }
    .btn{
      height:46px; border:0; border-radius:23px; cursor:pointer;
      font-weight:900; font-size:1.08rem;
      transition:transform .12s ease, filter .12s ease, box-shadow .12s ease;
      margin-left:30px;
    }
    .answer{
      height:46px; width:130px; border:0; border-radius:24px;
      background:#36B24A; color:#fff; font-weight:900; font-size:1.1rem;
      box-shadow:0 2px 6px rgba(0,0,0,.22); cursor:pointer;
      transition:transform .12s ease, filter .12s ease, box-shadow .12s ease;
    }
    .answer:hover{filter:brightness(1.05); transform:translateY(-1px); box-shadow:0 4px 10px rgba(0,0,0,.25)}
    .answer:active{transform:translateY(1px); box-shadow:0 1px 4px rgba(0,0,0,.2)}

    .puzzle{display:flex; align-items:center; justify-content:center}
    .puzzle-card{
      background:#fff; border-radius:10px; padding:10px;
      box-shadow:0 8px 30px rgba(0,0,0,.25);
      max-width:900px; width:88%;
    }
    .puzzle-card img{display:block; width:100%; height:auto; border-radius:6px}

    .timerbar{
      position:fixed; left:0; right:0; bottom:0; z-index:2;
      background:rgba(0,0,0,.28); backdrop-filter:blur(2px);
      display:flex; align-items:center; justify-content:center;
      gap:12px; padding:14px 16px;
    }
    .timer-icon{width:56px; height:56px; object-fit:contain}
    .time-left{font-weight:900; font-size:1.8rem; text-shadow:0 2px 8px rgba(0,0,0,.35)}

    .back{background-color:#36B24A;}
    .mini{background-color:#ff9f2c;}
    .back:hover{filter:brightness(1.08);transform:translateY(-2px);}
    .mini:hover{filter:brightness(1.1);transform:translateY(-2px);}

    .overlay{position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:5; background:rgba(0,0,0,.45)}
    .panel{text-align:center; padding:18px; animation:pop .18s ease}
    @keyframes pop{from{transform:scale(.93);opacity:0} to{transform:scale(1);opacity:1}}
    .alert-img{width:min(520px,78vw); height:auto; display:block; margin:0 auto 16px}
    .cta{color:#fff; border:0; cursor:pointer; font-weight:900; padding:12px 26px; border-radius:26px; box-shadow:0 2px 8px rgba(0,0,0,.25); transition:transform .12s ease, filter .12s ease}
    .cta:hover{transform:translateY(-1px); filter:brightness(1.05)}
    .next{background:#ff9f2c}
    .retry{background:#e63946}

    .out-banner{
      position:fixed; top:86px; left:16px; z-index:4;
      display:none; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(30,30,30,.92), rgba(30,30,30,.85));
      border:2px solid rgba(255,255,255,.12);
      border-radius:18px; padding:12px 16px;
      box-shadow:0 10px 26px rgba(0,0,0,.35);
      animation:pop-slide .24s ease forwards;
    }
    .out-banner.show{display:flex}
    .out-banner img{width:28px; height:28px; object-fit:contain}
    .out-banner .txt{font-weight:900; letter-spacing:.2px}
    .out-banner .mini{padding:8px 14px; border-radius:14px}

    @keyframes pop-slide{
      from{opacity:0; transform:translateY(-10px) scale(.96)}
      to{opacity:1; transform:translateY(0) scale(1)}
    }

    @media (max-width:1100px){
      .wrap{grid-template-columns:1fr}
      .board{min-height:600px}
      .board-inner{width:70%; height:72%}
      .answers{grid-template-columns:repeat(2, minmax(120px,1fr))}
    }
  </style>
</head>
<body class="page">
  <audio id="bgm"   src="assets/sounds/bgm2.mp3" loop preload="auto"></audio>
  <audio id="click" src="assets/sounds/click.mp3" preload="auto"></audio>

  <nav class="nav">
    <div class="lives" id="lives"></div>
    <div class="score">Score - <span id="score">0</span></div>
    <div class="user-actions">
      <div class="username" id="username">User Name</div>
      <button class="logout" id="logoutBtn">Logout</button>
    </div>
  </nav>

  <div id="outBanner" class="out-banner" aria-live="polite" aria-hidden="true">
    <img src="images/Life.png" alt="No lives">
    <div class="txt">You're out of lives! Play a mini-game to earn 1 life.</div>
    <button class="mini cta" id="miniBannerBtn">Play Mini Game</button>
  </div>

  <!-- mini game + levels icons -->
  <img src="images/Mini game icon.png" class="mini-icon" alt="Mini Game" onclick="openMiniChooser()"/>
  <img src="images/Levels icon.png" class="levels-icon" alt="Levels" onclick="goLevels()"/>

  <section class="wrap">
    <aside class="board">
      <div class="board-inner">
        <div class="level-tag">Level - <span id="level">01</span></div>
        <div class="question" id="questionText">What number replaces the banana ?</div>
        <div class="answers">
          <button class="btn answer" id="a0" data-idx="0">09</button>
          <button class="btn answer" id="a1" data-idx="1">05</button>
          <button class="btn answer" id="a2" data-idx="2">23</button>
          <button class="btn answer" id="a3" data-idx="3">07</button>
        </div>
      </div>
    </aside>

    <div class="puzzle">
      <div class="puzzle-card">
        <img id="puzzleImg" src="" alt="Banana Puzzle"/>
      </div>
    </div>
  </section>

  <footer class="timerbar">
    <img class="timer-icon" src="images/Timer icon.png" alt="Timer"/>
    <div class="time-left"><span id="time">40</span> s</div>
  </footer>

  <div class="overlay" id="overlay">
    <div class="panel" id="panel"></div>
  </div>

<script>
  const API_BASE = 'api/';

  const $  = s => document.querySelector(s);
  const $$ = s => document.querySelectorAll(s);

  const bgm   = $('#bgm');
  const click = $('#click');
  let muted   = localStorage.getItem('muted') === 'true';
  bgm.muted   = muted;
  bgm.volume  = 0.35;

  const enableBgmOnce = () => {
    if (!muted) bgm.play().catch(()=>{});
    document.removeEventListener('click', enableBgmOnce);
    document.removeEventListener('keydown', enableBgmOnce);
  };
  document.addEventListener('click', enableBgmOnce);
  document.addEventListener('keydown', enableBgmOnce);

  function sfx(){ try{ click.cloneNode().play(); }catch(e){} }

  // logout
  $('#logoutBtn').addEventListener('click', async () => {
    sfx();
    try { await fetch(API_BASE + 'logout.php', { method:'POST' }); } catch(e){}
    localStorage.removeItem('session_user');
    location.href = 'auth.html';
  });

  function goLevels(){ sfx(); location.href='levels.html'; }
  window.goLevels = goLevels;

  // --- game state ---
  let lives     = 3;
  let level     = 1;
  let score     = 0;
  let timeLimit = 40;
  let timeLeft  = 40;
  let timer     = null;

  function renderLives(){
    const box = $('#lives'); box.innerHTML = '';
    for (let i=0;i<lives;i++){
      const img = document.createElement('img');
      img.src = 'images/Life.png';
      img.alt = 'life';
      img.className = 'life';
      box.appendChild(img);
    }
    refreshOutBanner();
  }
  function updateScore(){
    $('#score').textContent = String(score).padStart(2,'0');
  }
  function updateLevel(){
    $('#level').textContent = String(level).padStart(2,'0');
  }

  function disableAnswers(disabled=true){
    $$('.answer').forEach(b => { b.disabled = disabled; });
  }

  // timer
  function resetTimer(){
    clearInterval(timer);
    if (!Number.isFinite(timeLimit) || timeLimit <= 0) timeLimit = 40;
    timeLeft = timeLimit;
    $('#time').textContent = timeLeft;
    timer = setInterval(()=>{
      timeLeft--;
      $('#time').textContent = timeLeft;
      if (timeLeft <= 0){
        clearInterval(timer);
        handleTimeout();
      }
    },1000);
  }

  // out-of-lives banner
  function refreshOutBanner(){
    const b = $('#outBanner');
    if (lives <= 0){
      b.classList.add('show');
      b.setAttribute('aria-hidden','false');
    } else {
      b.classList.remove('show');
      b.setAttribute('aria-hidden','true');
    }
  }

  $('#miniBannerBtn').addEventListener('click', () => {
    sfx();
    openMiniChooser();
  });

  // overlay helpers
  function showOverlay(imgPath, mode){
    const p = $('#panel');
    let btnLabel = 'Next';
    if (mode === 'retry')    btnLabel = 'Play Again';
    if (mode === 'finished') btnLabel = 'View Levels';

    p.innerHTML = `
      <img class="alert-img" src="${imgPath}" alt="${mode}">
      <button class="cta ${mode==='next'?'next':(mode==='retry'?'retry':'back')}" id="ctaBtn">${btnLabel}</button>
    `;
    const overlay = $('#overlay');
    overlay.style.display = 'flex';
    $('#ctaBtn').addEventListener('click', () => {
      sfx();
      overlay.style.display = 'none';
      if (mode === 'next' || mode === 'retry') {
        if (lives > 0) {
          loadPuzzle();
        } else {
          showOutOfLives();
        }
      } else if (mode === 'finished') {
        location.href = 'levels.html';
      }
    });
  }

  // popup when 0 lives – choose mini game
  function showOutOfLives(){
    clearInterval(timer);
    disableAnswers(true);

    const p = $('#panel');
    p.innerHTML = `
      <img class="alert-img" src="images/Lost.png" alt="No lives">
      <p style="color:#fff;font-weight:900;margin-bottom:18px;">
        You are out of lives. Play a mini game to earn 1 life.
      </p>
      <div style="display:flex; justify-content:center; gap:18px; flex-wrap:wrap;">
        <button class="cta mini" id="mini1Btn">Mini Game 1</button>
        <button class="cta mini" id="mini2Btn">Mini Game 2</button>
      </div>
    `;
    const overlay = $('#overlay');
    overlay.style.display = 'flex';

    $('#mini1Btn').addEventListener('click', ()=>{
      sfx();
      overlay.style.display = 'none';
      location.href = 'mini1.html';
    });
    $('#mini2Btn').addEventListener('click', ()=>{
      sfx();
      overlay.style.display = 'none';
      location.href = 'mini.html';
    });
  }

  // same chooser when clicking the mini icon
  function openMiniChooser(){
    clearInterval(timer);
    disableAnswers(true);

    const p = $('#panel');
    p.innerHTML = `
      <p style="color:#fff;font-weight:900;margin-bottom:18px;">
        Choose a mini game to play and earn extra lives.
      </p>
      <div style="display:flex; justify-content:center; gap:18px; flex-wrap:wrap;">
        <button class="cta mini" id="mini1Btn">Mini Game 1</button>
        <button class="cta mini" id="mini2Btn">Mini Game 2</button>
        <button class="cta back" id="closeMini">Back to Puzzle</button>
      </div>
    `;
    const overlay = $('#overlay');
    overlay.style.display = 'flex';

    $('#mini1Btn').addEventListener('click', ()=>{
      sfx();
      overlay.style.display = 'none';
      location.href = 'mini1.html';
    });
    $('#mini2Btn').addEventListener('click', ()=>{
      sfx();
      overlay.style.display = 'none';
      location.href = 'mini.html';
    });
    $('#closeMini').addEventListener('click', ()=>{
      sfx();
      overlay.style.display = 'none';
      if (lives > 0) {
        disableAnswers(false);
        resetTimer();
      }
    });
  }
  window.openMiniChooser = openMiniChooser;

  // --- load user + state (username + lives per user) ---
  async function loadUserAndState() {
    try {
      const res = await fetch(API_BASE + 'me.php', { cache:'no-store' });
      if (!res.ok) { location.href = 'auth.html'; return; }

      const data = await res.json();
      if (!data.ok) { location.href = 'auth.html'; return; }

      const user  = data.user  || {};
      const state = data.state || {};

      const uname =
        user.username ||
        user.name ||
        data.username ||
        localStorage.getItem('session_user') ||
        'Player';
      $('#username').textContent = uname;

      level     = parseInt(state.current_level     ?? level ?? 1);
      lives     = parseInt(state.lives             ?? lives ?? 3);
      score     = parseInt(state.score             ?? score ?? 0);
      timeLimit = parseInt(state.seconds_per_level ?? timeLimit ?? 40);

      if (isNaN(level) || level < 1) level = 1;
      if (isNaN(lives) || lives < 0) lives = 0;
      if (isNaN(score) || score < 0) score = 0;

      updateLevel();
      updateScore();
      renderLives();
    } catch (e) {
      console.error('Error calling me.php:', e);
      location.href = 'auth.html';
    }
  }

  // --- load puzzle ---
  async function loadPuzzle() {
    if (lives <= 0){
      renderLives();
      showOutOfLives();
      return;
    }

    disableAnswers(true);
    try {
      const res = await fetch(API_BASE + 'get_puzzle.php', { cache:'no-store' });
      const data = await res.json();
      console.log('get_puzzle.php:', data);

      if (!data.ok) {
        const reason = data.reason || data.msg || 'unknown';
        if (reason === 'finished') {
          showOverlay('images/Game over.png', 'finished');
        } else if (reason === 'no_lives') {
          lives = 0;
          renderLives();
          showOutOfLives();
        } else {
          alert('Error loading puzzle: ' + reason);
        }
        return;
      }

      level     = parseInt(data.level ?? level);
      lives     = parseInt(data.lives ?? lives);
      score     = parseInt(data.score ?? score);
      timeLimit = parseInt(data.secondsAllowed ?? timeLimit ?? 40);

      if (isNaN(level) || level < 1) level = 1;
      if (isNaN(lives) || lives < 0) lives = 0;
      if (isNaN(score) || score < 0) score = 0;

      updateLevel();
      updateScore();
      renderLives();

      const imgUrl   = data.questionUrl || data.img;
      const question = data.questionText || data.question;
      if (imgUrl) {
        $('#puzzleImg').src = imgUrl;
      }
      if (question) {
        $('#questionText').textContent = question;
      }

      const answers = data.answers || [];
      answers.forEach((v,i)=>{
        const b = $('#a'+i);
        if (!b) return;
        b.textContent   = String(v).padStart(2,'0');
        b.dataset.value = v;
        b.disabled      = false;
      });

      disableAnswers(false);
      resetTimer();
    } catch (e) {
      console.error('Network/JS error in loadPuzzle:', e);
      alert('Server error loading puzzle.');
    }
  }

  // --- timeout ---
  async function handleTimeout() {
    disableAnswers(true);
    try {
      const res  = await fetch(API_BASE + 'save_game.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ event:'timeout' })
      });
      const data = await res.json();

      lives = data.lives ?? lives;
      score = data.score ?? score;
      level = data.level ?? level;

      if (isNaN(lives) || lives < 0) lives = 0;

      updateScore();
      updateLevel();
      renderLives();

      if (data.gameOver || lives <= 0 || data.result === 'no_lives') {
        lives = 0;
        renderLives();
        showOutOfLives();
      } else {
        showOverlay('images/Time out.png', 'retry');
      }
    } catch (e) {
      console.error('Network error saving timeout:', e);
      alert('Error saving timeout.');
    }
  }

  // --- answer click ---
  $$('.answer').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (lives <= 0) {
        showOutOfLives();
        return;
      }

      sfx();
      disableAnswers(true);
      clearInterval(timer);

      const val = Number(btn.dataset.value);
      try {
        const res  = await fetch(API_BASE + 'save_game.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ event:'answer', answer: val })
        });
        const data = await res.json();

        lives = data.lives ?? lives;
        score = data.score ?? score;
        level = data.level ?? level;

        if (isNaN(lives) || lives < 0) lives = 0;

        updateScore();
        updateLevel();
        renderLives();

        if (data.gameOver || lives <= 0 || data.result === 'no_lives') {
          if (data.finished) {
            showOverlay('images/Game over.png', 'finished');
          } else {
            lives = 0;
            renderLives();
            showOutOfLives();
          }
          return;
        }

        if (data.result === 'correct') {
          showOverlay('images/Answer is correct.png', 'next');
        } else if (data.result === 'wrong') {
          showOverlay('images/Answer is wrong.png', 'retry');
        } else if (data.result === 'timeout') {
          showOverlay('images/Time out.png', 'retry');
        } else {
          loadPuzzle();
        }
      } catch (e) {
        console.error('Network error saving answer:', e);
        alert('Error saving answer.');
        disableAnswers(false);
      }
    });
  });

  // --- init ---
  (async function init() {
    await loadUserAndState();
    if (lives <= 0){
      showOutOfLives();
    } else {
      await loadPuzzle();
    }
  })();
</script>
</body>
</html>
