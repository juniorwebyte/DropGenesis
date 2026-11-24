/*
 * ==========================================================================
 * DropGenesis — Fundo em scroll (crossfade)
 * ==========================================================================
 *
 * Copyright (c) Webyte Desenvolvimentos. All rights reserved.
 * https://webytehub.com/licencas
 *
 * --------------------------------------------------------------------------
 *
 * Motor único do fundo animado do instalador.
 *
 * Antes, quatro das cinco páginas apenas sorteavam UMA imagem no PHP e a
 * fixavam no CSS — o "scroll" não existia nelas. A quinta (check/) tinha um
 * `setInterval` que trocava a imagem sem esperar o download: em conexão lenta
 * a tela piscava para o gradiente vazio no meio da transição, e o timer
 * continuava girando com a aba em segundo plano, baixando megabytes que
 * ninguém veria.
 *
 * Este módulo mantém exatamente o mesmo tratamento visual das páginas (o
 * gradiente de sobreposição vem do PHP, não é redefinido aqui) e cuida só do
 * comportamento: pré-carregar, decodificar, só então trocar.
 *
 * Configuração esperada em `window.DROP_SCROLL`:
 *   images   {string[]} rotas absolutas, em ordem natural
 *   overlay  {string}   gradiente de sobreposição da página
 *   interval {number}   intervalo entre trocas, em ms
 *   mode     {'single'|'dual'}
 *   base     {string}   seletor da camada de base
 *   next     {string}   seletor da camada de transição (modo dual)
 *   smoke    {string}   seletor opcional do véu de fumaça (modo dual)
 * ==========================================================================
 */
(function () {
  'use strict';

  var cfg = window.DROP_SCROLL;
  if (!cfg || !cfg.images || cfg.images.length < 2) {
    // Uma imagem só (ou nenhuma) não tem o que rodar: o CSS já a exibe.
    return;
  }

  var mode = cfg.mode === 'dual' ? 'dual' : 'single';
  var interval = Math.max(3000, parseInt(cfg.interval, 10) || 8000);
  var images = cfg.images;
  var overlay = cfg.overlay || '';

  /* ── Quando NÃO animar ────────────────────────────────────────────────
     Cada um destes casos é um pedido explícito do usuário ou do ambiente
     para não gastar banda/CPU. O fundo estático continua lá; só a rotação
     é suspensa. */
  function shouldStayStatic() {
    try {
      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return true;
      }
    } catch (e) { /* matchMedia ausente: segue */ }

    var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (conn) {
      if (conn.saveData === true) return true;
      var slow = /^(slow-2g|2g)$/;
      if (typeof conn.effectiveType === 'string' && slow.test(conn.effectiveType)) return true;
    }
    return false;
  }

  if (shouldStayStatic()) return;

  /* ── Camadas ─────────────────────────────────────────────────────────── */
  var baseEl = document.querySelector(cfg.base || '#db-master-bg');
  if (!baseEl) return;

  var nextEl;
  var smokeEl = cfg.smoke ? document.querySelector(cfg.smoke) : null;

  if (mode === 'dual') {
    nextEl = document.querySelector(cfg.next || '#bg-t');
    if (!nextEl) return;
  } else {
    // A camada gêmea é criada aqui em vez de ficar no HTML: o CSS da página
    // já a estiliza pelo id, e assim nenhuma página precisa de marcação nova.
    nextEl = document.getElementById('db-master-bg-next');
    if (!nextEl) {
      nextEl = document.createElement('div');
      nextEl.id = 'db-master-bg-next';
      nextEl.setAttribute('aria-hidden', 'true');
      baseEl.parentNode.insertBefore(nextEl, baseEl.nextSibling);
    }
  }

  /* ── Composição do valor CSS ─────────────────────────────────────────── */
  function cssUrl(src) {
    // Aspas simples dentro do nome do arquivo quebrariam o valor CSS.
    return "url('" + String(src).replace(/'/g, "%27") + "')";
  }

  function paint(el, src) {
    var image = cssUrl(src);
    if (mode === 'dual') {
      // `.bg-layer` já define position/size no CSS da página.
      el.style.backgroundImage = overlay ? overlay + ', ' + image : image;
    } else {
      el.style.background = (overlay ? overlay + ', ' : '') + image + ' center/cover no-repeat';
    }
  }

  /* ── Pré-carregamento ────────────────────────────────────────────────── */
  var preloaded = Object.create(null);

  function preload(src) {
    if (preloaded[src]) return preloaded[src];

    preloaded[src] = new Promise(function (resolve, reject) {
      var img = new Image();
      img.decoding = 'async';
      img.onload = function () {
        // `decode()` garante que o bitmap está pronto: sem isso o navegador
        // ainda decodifica durante o fade e a transição engasga.
        if (typeof img.decode === 'function') {
          img.decode().then(resolve).catch(resolve);
        } else {
          resolve();
        }
      };
      img.onerror = reject;
      img.src = src;
    });

    return preloaded[src];
  }

  /* ── Rotação ─────────────────────────────────────────────────────────── */
  var current = 0;
  var timer = null;
  var running = false;

  function wait(ms) {
    return new Promise(function (resolve) { setTimeout(resolve, ms); });
  }

  function transitionDuration(el) {
    try {
      var value = window.getComputedStyle(el).transitionDuration || '0s';
      var first = value.split(',')[0].trim();
      return first.indexOf('ms') > -1 ? parseFloat(first) : parseFloat(first) * 1000;
    } catch (e) {
      return 1500;
    }
  }

  function advance() {
    if (running || document.hidden) return Promise.resolve();
    running = true;

    var index = (current + 1) % images.length;
    var src = images[index];

    return preload(src)
      .then(function () {
        // A aba pode ter sido escondida durante o download.
        if (document.hidden) return;

        var fade = transitionDuration(nextEl) || 1500;

        if (smokeEl) {
          smokeEl.style.opacity = '1';
          return wait(fade).then(function () { return fade; });
        }
        return fade;
      })
      .then(function (fade) {
        if (document.hidden || typeof fade !== 'number') return;

        paint(nextEl, src);
        nextEl.style.opacity = '1';

        return wait(fade).then(function () {
          // A base recebe a mesma imagem por baixo da camada opaca; a troca
          // acontece sem nenhum quadro intermediário visível.
          paint(baseEl, src);

          var previous = nextEl.style.transition;
          nextEl.style.transition = 'none';
          nextEl.style.opacity = '0';
          void nextEl.offsetHeight; // força o reflow antes de devolver a transição
          nextEl.style.transition = previous;

          if (smokeEl) smokeEl.style.opacity = '0';
          current = index;

          // Aquece a próxima enquanto a tela está parada, para que a troca
          // seguinte não dependa da rede.
          preload(images[(index + 1) % images.length]).catch(function () {});
        });
      })
      .catch(function () {
        // Imagem indisponível (arquivo removido, 404): pula para a próxima na
        // rodada seguinte em vez de travar a sequência.
        current = index;
      })
      .then(function () {
        running = false;
      });
  }

  function start() {
    if (timer) return;
    timer = setInterval(advance, interval);
  }

  function stop() {
    if (!timer) return;
    clearInterval(timer);
    timer = null;
  }

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) stop();
    else start();
  });

  // A rotação só começa depois que a página terminou de carregar: o
  // instalador é um formulário, e nenhum pixel de enfeite deve disputar banda
  // com ele. A primeira imagem já veio pelo CSS.
  function boot() {
    preload(images[1 % images.length]).catch(function () {});
    start();
  }

  if (document.readyState === 'complete') boot();
  else window.addEventListener('load', boot, { once: true });
})();
