(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof module === 'object' && module.exports) {
    // Node/CommonJS
    module.exports = factory(require('jquery'));
  } else {
    // Browser globals
    factory(window.jQuery);
  }
}(($) => {
  $.extend($.summernote.plugins, {
    specialchars(context) {
      const self = this;
      const { ui } = $.summernote;

      const $editor = context.layoutInfo.editor;
      const { options } = context;
      const lang = options.langInfo;

      const KEY = {
        UP: 38,
        DOWN: 40,
        LEFT: 37,
        RIGHT: 39,
        ENTER: 13,
      };
      const COLUMN_LENGTH = 12;
      const COLUMN_WIDTH = 35;

      let currentColumn = 0;
      let currentRow = 0;
      let totalColumn = 0;
      let totalRow = 0;

      // special characters data set
      const specialCharDataSet = [
        '&quot;', '&amp;', '&lt;', '&gt;', '&iexcl;', '&cent;',
        '&pound;', '&curren;', '&yen;', '&brvbar;', '&sect;',
        '&uml;', '&copy;', '&ordf;', '&laquo;', '&not;',
        '&reg;', '&macr;', '&deg;', '&plusmn;', '&sup2;',
        '&sup3;', '&acute;', '&micro;', '&para;', '&middot;',
        '&cedil;', '&sup1;', '&ordm;', '&raquo;', '&frac14;',
        '&frac12;', '&frac34;', '&iquest;', '&times;', '&divide;',
        '&fnof;', '&circ;', '&tilde;', '&ndash;', '&mdash;',
        '&lsquo;', '&rsquo;', '&sbquo;', '&ldquo;', '&rdquo;',
        '&bdquo;', '&dagger;', '&Dagger;', '&bull;', '&hellip;',
        '&permil;', '&prime;', '&Prime;', '&lsaquo;', '&rsaquo;',
        '&oline;', '&frasl;', '&euro;', '&image;', '&weierp;',
        '&real;', '&trade;', '&alefsym;', '&larr;', '&uarr;',
        '&rarr;', '&darr;', '&harr;', '&crarr;', '&lArr;',
        '&uArr;', '&rArr;', '&dArr;', '&hArr;', '&forall;',
        '&part;', '&exist;', '&empty;', '&nabla;', '&isin;',
        '&notin;', '&ni;', '&prod;', '&sum;', '&minus;',
        '&lowast;', '&radic;', '&prop;', '&infin;', '&ang;',
        '&and;', '&or;', '&cap;', '&cup;', '&int;',
        '&there4;', '&sim;', '&cong;', '&asymp;', '&ne;',
        '&equiv;', '&le;', '&ge;', '&sub;', '&sup;',
        '&nsub;', '&sube;', '&supe;', '&oplus;', '&otimes;',
        '&perp;', '&sdot;', '&lceil;', '&rceil;', '&lfloor;',
        '&rfloor;', '&loz;', '&spades;', '&clubs;', '&hearts;',
        '&diams;',
      ];

      context.memo('button.specialchars', () => ui.button({
        contents: '<i class="fa fa-font fa-flip-vertical"></i>',
        tooltip: lang.specialChar.specialChar,
        click() {
          self.show();
        },
      }).render());

      /**
             * Make Special Characters Table
             *
             * @member plugin.specialChar
             * @private
             * @return {jQuery}
             */
      this.makeSpecialCharSetTable = function () {
        const $table = $('<table></table>');
        $.each(specialCharDataSet, (idx, text) => {
          const $td = $('<td></td>').addClass('note-specialchar-node');
          const $tr = (idx % COLUMN_LENGTH === 0) ? $('<tr></tr>') : $table.find('tr').last();

          const $button = ui.button({
            callback($node) {
              $node.html(text);
              $node.attr('title', text);
              $node.attr('data-value', encodeURIComponent(text));
              $node.css({
                width: COLUMN_WIDTH,
                'margin-right': '2px',
                'margin-bottom': '2px',
              });
            },
          }).render();

          $td.append($button);

          $tr.append($td);
          if (idx % COLUMN_LENGTH === 0) {
            $table.append($tr);
          }
        });

        totalRow = $table.find('tr').length;
        totalColumn = COLUMN_LENGTH;

        return $table;
      };

      this.initialize = function () {
        const $container = options.dialogsInBody ? $(document.body) : $editor;

        const body = `<div class="form-group row-fluid">${this.makeSpecialCharSetTable()[0].outerHTML}</div>`;

        this.$dialog = ui.dialog({
          title: lang.specialChar.select,
          body,
        }).render().appendTo($container);
      };

      this.show = function () {
        const text = context.invoke('editor.getSelectedText');
        context.invoke('editor.saveRange');
        this.showSpecialCharDialog(text).then((selectChar) => {
          context.invoke('editor.restoreRange');

          // build node
          const $node = $('<span></span>').html(selectChar)[0];

          if ($node) {
            // insert video node
            context.invoke('editor.insertNode', $node);
          }
        }).fail(() => {
          context.invoke('editor.restoreRange');
        });
      };

      /**
             * show image dialog
             *
             * @param {jQuery} $dialog
             * @return {Promise}
             */
      this.showSpecialCharDialog = function (text) {
        return $.Deferred((deferred) => {
          const $specialCharDialog = self.$dialog;
          const $specialCharNode = $specialCharDialog.find('.note-specialchar-node');
          let $selectedNode = null;
          const ARROW_KEYS = [KEY.UP, KEY.DOWN, KEY.LEFT, KEY.RIGHT];
          const ENTER_KEY = KEY.ENTER;

          function addActiveClass($target) {
            if (!$target) {
              return;
            }
            $target.find('button').addClass('active');
            $selectedNode = $target;
          }

          function removeActiveClass($target) {
            $target.find('button').removeClass('active');
            $selectedNode = null;
          }

          // find next node
          function findNextNode(row, column) {
            let findNode = null;
            $.each($specialCharNode, (idx, $node) => {
              const findRow = Math.ceil((idx + 1) / COLUMN_LENGTH);
              const findColumn = ((idx + 1) % COLUMN_LENGTH === 0) ? COLUMN_LENGTH : (idx + 1) % COLUMN_LENGTH;
              if (findRow === row && findColumn === column) {
                findNode = $node;
                return false;
              }
            });
            return $(findNode);
          }

          function arrowKeyHandler(keyCode) {
            // left, right, up, down key
            let $nextNode;
            const lastRowColumnLength = $specialCharNode.length % totalColumn;

            if (KEY.LEFT === keyCode) {
              if (currentColumn > 1) {
                currentColumn -= 1;
              } else if (currentRow === 1 && currentColumn === 1) {
                currentColumn = lastRowColumnLength;
                currentRow = totalRow;
              } else {
                currentColumn = totalColumn;
                currentRow -= 1;
              }
            } else if (KEY.RIGHT === keyCode) {
              if (currentRow === totalRow && lastRowColumnLength === currentColumn) {
                currentColumn = 1;
                currentRow = 1;
              } else if (currentColumn < totalColumn) {
                currentColumn += 1;
              } else {
                currentColumn = 1;
                currentRow += 1;
              }
            } else if (KEY.UP === keyCode) {
              if (currentRow === 1 && lastRowColumnLength < currentColumn) {
                currentRow = totalRow - 1;
              } else {
                currentRow -= 1;
              }
            } else if (KEY.DOWN === keyCode) {
              currentRow += 1;
            }

            if (currentRow === totalRow && currentColumn > lastRowColumnLength) {
              currentRow = 1;
            } else if (currentRow > totalRow) {
              currentRow = 1;
            } else if (currentRow < 1) {
              currentRow = totalRow;
            }

            $nextNode = findNextNode(currentRow, currentColumn);

            if ($nextNode) {
              removeActiveClass($selectedNode);
              addActiveClass($nextNode);
            }
          }

          function enterKeyHandler() {
            if (!$selectedNode) {
              return;
            }

            deferred.resolve(decodeURIComponent($selectedNode.find('button').attr('data-value')));
            $specialCharDialog.modal('hide');
          }

          function keyDownEventHandler(event) {
            event.preventDefault();
            const { keyCode } = event;
            if (keyCode === undefined || keyCode === null) {
              return;
            }
            // check arrowKeys match
            if (ARROW_KEYS.indexOf(keyCode) > -1) {
              if ($selectedNode === null) {
                addActiveClass($specialCharNode.eq(0));
                currentColumn = 1;
                currentRow = 1;
                return;
              }
              arrowKeyHandler(keyCode);
            } else if (keyCode === ENTER_KEY) {
              enterKeyHandler();
            }
            return false;
          }

          // remove class
          removeActiveClass($specialCharNode);

          // find selected node
          if (text) {
            for (let i = 0; i < $specialCharNode.length; i++) {
              const $checkNode = $($specialCharNode[i]);
              if ($checkNode.text() === text) {
                addActiveClass($checkNode);
                currentRow = Math.ceil((i + 1) / COLUMN_LENGTH);
                currentColumn = (i + 1) % COLUMN_LENGTH;
              }
            }
          }

          ui.onDialogShown(self.$dialog, () => {
            $(document).on('keydown', keyDownEventHandler);

            self.$dialog.find('button').tooltip();

            $specialCharNode.on('click', (event) => {
              event.preventDefault();
              deferred.resolve(decodeURIComponent($(event.currentTarget).find('button').attr('data-value')));
              ui.hideDialog(self.$dialog);
            });
          });

          ui.onDialogHidden(self.$dialog, () => {
            $specialCharNode.off('click');

            self.$dialog.find('button').tooltip();

            $(document).off('keydown', keyDownEventHandler);

            if (deferred.state() === 'pending') {
              deferred.reject();
            }
          });

          ui.showDialog(self.$dialog);
        });
      };
    },
  });
}));
