import {
  on,
  off,
  css,
  throttle,
  cancelThrottle,
  scrollBy,
  getParentAutoScrollElement,
  expando,
  getRect,
  getWindowScrollingElement,
} from '../../src/utils.js';

import Sortable from '../../src/Sortable.js';

import { Edge, IE11OrLess, Safari } from '../../src/BrowserInfo.js';

let autoScrolls = [];
let scrollEl;
let scrollRootEl;
let scrolling = false;
let lastAutoScrollX;
let lastAutoScrollY;
let touchEvt;
let pointerElemChangedInterval;

function AutoScrollPlugin() {
  function AutoScroll() {
    this.defaults = {
      scroll: true,
      forceAutoScrollFallback: false,
      scrollSensitivity: 30,
      scrollSpeed: 10,
      bubbleScroll: true,
    };

    // Bind all private methods
    for (const fn in this) {
      if (fn.charAt(0) === '_' && typeof this[fn] === 'function') {
        this[fn] = this[fn].bind(this);
      }
    }
  }

  AutoScroll.prototype = {
    dragStarted({ originalEvent }) {
      if (this.sortable.nativeDraggable) {
        on(document, 'dragover', this._handleAutoScroll);
      } else if (this.options.supportPointer) {
        on(document, 'pointermove', this._handleFallbackAutoScroll);
      } else if (originalEvent.touches) {
        on(document, 'touchmove', this._handleFallbackAutoScroll);
      } else {
        on(document, 'mousemove', this._handleFallbackAutoScroll);
      }
    },

    dragOverCompleted({ originalEvent }) {
      // For when bubbling is canceled and using fallback (fallback 'touchmove' always reached)
      if (!this.options.dragOverBubble && !originalEvent.rootEl) {
        this._handleAutoScroll(originalEvent);
      }
    },

    drop() {
      if (this.sortable.nativeDraggable) {
        off(document, 'dragover', this._handleAutoScroll);
      } else {
        off(document, 'pointermove', this._handleFallbackAutoScroll);
        off(document, 'touchmove', this._handleFallbackAutoScroll);
        off(document, 'mousemove', this._handleFallbackAutoScroll);
      }

      clearPointerElemChangedInterval();
      clearAutoScrolls();
      cancelThrottle();
    },

    nulling() {
      touchEvt =			scrollRootEl =			scrollEl =			scrolling =			pointerElemChangedInterval =			lastAutoScrollX =			lastAutoScrollY = null;

      autoScrolls.length = 0;
    },

    _handleFallbackAutoScroll(evt) {
      this._handleAutoScroll(evt, true);
    },

    _handleAutoScroll(evt, fallback) {
      const x = (evt.touches ? evt.touches[0] : evt).clientX;
      const y = (evt.touches ? evt.touches[0] : evt).clientY;

      const elem = document.elementFromPoint(x, y);

      touchEvt = evt;

      // IE does not seem to have native autoscroll,
      // Edge's autoscroll seems too conditional,
      // MACOS Safari does not have autoscroll,
      // Firefox and Chrome are good
      if (fallback || this.options.forceAutoScrollFallback || Edge || IE11OrLess || Safari) {
        autoScroll(evt, this.options, elem, fallback);

        // Listener for pointer element change
        let ogElemScroller = getParentAutoScrollElement(elem, true);
        if (
          scrolling
					&& (
					  !pointerElemChangedInterval
						|| x !== lastAutoScrollX
						|| y !== lastAutoScrollY
					)
        ) {
          pointerElemChangedInterval && clearPointerElemChangedInterval();
          // Detect for pointer elem change, emulating native DnD behaviour
          pointerElemChangedInterval = setInterval(() => {
            const newElem = getParentAutoScrollElement(document.elementFromPoint(x, y), true);
            if (newElem !== ogElemScroller) {
              ogElemScroller = newElem;
              clearAutoScrolls();
            }
            autoScroll(evt, this.options, newElem, fallback);
          }, 10);
          lastAutoScrollX = x;
          lastAutoScrollY = y;
        }
      } else {
        // if DnD is enabled (and browser has good autoscrolling), first autoscroll will already scroll, so get parent autoscroll of first autoscroll
        if (!this.options.bubbleScroll || getParentAutoScrollElement(elem, true) === getWindowScrollingElement()) {
          clearAutoScrolls();
          return;
        }
        autoScroll(evt, this.options, getParentAutoScrollElement(elem, false), false);
      }
    },
  };

  return Object.assign(AutoScroll, {
    pluginName: 'scroll',
    initializeByDefault: true,
  });
}

function clearAutoScrolls() {
  autoScrolls.forEach((autoScroll) => {
    clearInterval(autoScroll.pid);
  });
  autoScrolls = [];
}

function clearPointerElemChangedInterval() {
  clearInterval(pointerElemChangedInterval);
}

const autoScroll = throttle((evt, options, rootEl, isFallback) => {
  // Bug: https://bugzilla.mozilla.org/show_bug.cgi?id=505521
  if (!options.scroll) return;
  const x = (evt.touches ? evt.touches[0] : evt).clientX;
  const y = (evt.touches ? evt.touches[0] : evt).clientY;
  const sens = options.scrollSensitivity;
  const speed = options.scrollSpeed;
  const winScroller = getWindowScrollingElement();

  let scrollThisInstance = false;
  let scrollCustomFn;

  // New scroll root, set scrollEl
  if (scrollRootEl !== rootEl) {
    scrollRootEl = rootEl;

    clearAutoScrolls();

    scrollEl = options.scroll;
    scrollCustomFn = options.scrollFn;

    if (scrollEl === true) {
      scrollEl = getParentAutoScrollElement(rootEl, true);
    }
  }

  let layersOut = 0;
  let currentParent = scrollEl;
  do {
    const	el = currentParent;
    const rect = getRect(el);

    const { top } = rect;
    const { bottom } = rect;
    const { left } = rect;
    const { right } = rect;

    const { width } = rect;
    const { height } = rect;

    let canScrollX;
    let canScrollY;

    const { scrollWidth } = el;
    const { scrollHeight } = el;

    const elCSS = css(el);

    const scrollPosX = el.scrollLeft;
    const scrollPosY = el.scrollTop;

    if (el === winScroller) {
      canScrollX = width < scrollWidth && (elCSS.overflowX === 'auto' || elCSS.overflowX === 'scroll' || elCSS.overflowX === 'visible');
      canScrollY = height < scrollHeight && (elCSS.overflowY === 'auto' || elCSS.overflowY === 'scroll' || elCSS.overflowY === 'visible');
    } else {
      canScrollX = width < scrollWidth && (elCSS.overflowX === 'auto' || elCSS.overflowX === 'scroll');
      canScrollY = height < scrollHeight && (elCSS.overflowY === 'auto' || elCSS.overflowY === 'scroll');
    }

    const vx = canScrollX && (Math.abs(right - x) <= sens && (scrollPosX + width) < scrollWidth) - (Math.abs(left - x) <= sens && !!scrollPosX);
    const vy = canScrollY && (Math.abs(bottom - y) <= sens && (scrollPosY + height) < scrollHeight) - (Math.abs(top - y) <= sens && !!scrollPosY);

    if (!autoScrolls[layersOut]) {
      for (let i = 0; i <= layersOut; i++) {
        if (!autoScrolls[i]) {
          autoScrolls[i] = {};
        }
      }
    }

    if (autoScrolls[layersOut].vx != vx || autoScrolls[layersOut].vy != vy || autoScrolls[layersOut].el !== el) {
      autoScrolls[layersOut].el = el;
      autoScrolls[layersOut].vx = vx;
      autoScrolls[layersOut].vy = vy;

      clearInterval(autoScrolls[layersOut].pid);

      if (vx != 0 || vy != 0) {
        scrollThisInstance = true;
        /* jshint loopfunc:true */
        autoScrolls[layersOut].pid = setInterval((function () {
          // emulate drag over during autoscroll (fallback), emulating native DnD behaviour
          if (isFallback && this.layer === 0) {
            Sortable.active._onTouchMove(touchEvt); // To move ghost if it is positioned absolutely
          }
          const scrollOffsetY = autoScrolls[this.layer].vy ? autoScrolls[this.layer].vy * speed : 0;
          const scrollOffsetX = autoScrolls[this.layer].vx ? autoScrolls[this.layer].vx * speed : 0;

          if (typeof (scrollCustomFn) === 'function') {
            if (scrollCustomFn.call(Sortable.dragged.parentNode[expando], scrollOffsetX, scrollOffsetY, evt, touchEvt, autoScrolls[this.layer].el) !== 'continue') {
              return;
            }
          }

          scrollBy(autoScrolls[this.layer].el, scrollOffsetX, scrollOffsetY);
        }).bind({ layer: layersOut }), 24);
      }
    }
    layersOut++;
  } while (options.bubbleScroll && currentParent !== winScroller && (currentParent = getParentAutoScrollElement(currentParent, false)));
  scrolling = scrollThisInstance; // in case another function catches scrolling as false in between when it is not
}, 30);

export default AutoScrollPlugin;
