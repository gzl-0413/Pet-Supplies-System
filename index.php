



<head>
    <style>.hero-banner {
  align-items: center;
  display: flex;
  height: 100vh;
  position: relative;
  justify-content: center;
  z-index: -1;
}

.hero-banner__title {
  color: #fff;
  font-size: 100px;
  font-weight: 700;
  padding: 0 20px;
  position: absolute;
  text-align: center;
  text-transform: uppercase;
}

.hero-banner__stroked-title {
  color: transparent;
  -webkit-text-stroke: 2px white;
  text-stroke: 2px white;
}

.hero-banner__image {
  border-radius: 20px;
  position: fixed;
  transform: rotate(-15deg);
  width: 450px;
}

.content {
  background-color: #eaeaea;
  font-size: 40px;
  padding: 120px 80px;
  height: 100vh; // Fake content height.
}

body {
  background-color: #000;
  font-family: Arial;
}

* {
  box-sizing: border-box;
}

 
.button-89 {
  --b: 3px;    
  --s: .45em;  
  --color: #373B44;
  
  padding: calc(.5em + var(--s)) calc(.9em + var(--s));
  color: var(--color);
  --_p: var(--s);
  background:
    conic-gradient(from 90deg at var(--b) var(--b),#0000 90deg,var(--color) 0)
    var(--_p) var(--_p)/calc(100% - var(--b) - 2*var(--_p)) calc(100% - var(--b) - 2*var(--_p));
  transition: .3s linear, color 0s, background-color 0s;
  outline: var(--b) solid #0000;
  outline-offset: .6em;
  font-size: 16px;

  border: 0;
margin-left: 750px;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
}

.button-89:hover,
.button-89:focus-visible{
  --_p: 0px;
  outline-color: var(--color);
  outline-offset: .05em;
}

.button-89:active {
  background: var(--color);
  color: #fff;
}
</style>

</head>

<div class="hero-banner">
  <div class="hero-banner__title" aria-hidden="true">Welcome</div>
  <img class="hero-banner__image" src="images/OIP.jpg"/>
  <h1 class="hero-banner__title hero-banner__stroked-title">Welcome</h1>
</div>

<div class="content">
<button class="button-89" role="button" onclick="window.location.href = 'User/index.php'">Start Shopping Journey</button>
</div>
