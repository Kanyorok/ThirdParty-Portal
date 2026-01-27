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
  // pull in some summernote core functions
  const { ui } = $.summernote;
  const { dom } = $.summernote;

  // define the popover plugin
  const DataBasicPlugin = function (context) {
    const self = this;
    const { options } = context;
    const lang = options.langInfo;

    self.icon = '<i class="fa fa-object-group"></i>';

    // add context menu button for dialog
    context.memo('button.databasic', () => ui.button({
      contents: self.icon,
      tooltip: lang.databasic.insert,
      click: context.createInvokeHandler('databasic.showDialog'),
    }).render());

    // add popover edit button
    context.memo('button.databasicDialog', () => ui.button({
      contents: self.icon,
      tooltip: lang.databasic.edit,
      click: context.createInvokeHandler('databasic.showDialog'),
    }).render());

    //  add popover size buttons
    context.memo('button.databasicSize100', () => ui.button({
      contents: '<span class="note-fontsize-10">100%</span>',
      tooltip: lang.image.resizeFull,
      click: context.createInvokeHandler('editor.resize', '1'),
    }).render());
    context.memo('button.databasicSize50', () => ui.button({
      contents: '<span class="note-fontsize-10">50%</span>',
      tooltip: lang.image.resizeHalf,
      click: context.createInvokeHandler('editor.resize', '0.5'),
    }).render());
    context.memo('button.databasicSize25', () => ui.button({
      contents: '<span class="note-fontsize-10">25%</span>',
      tooltip: lang.image.resizeQuarter,
      click: context.createInvokeHandler('editor.resize', '0.25'),
    }).render());

    self.events = {
      'summernote.init': function (we, e) {
        // update existing containers
        $('data.ext-databasic', e.editable).each(function () {
          self.setContent($(this));
        });
        // TODO: make this an undo snapshot...
      },
      'summernote.keyup summernote.mouseup summernote.change summernote.scroll': function () {
        self.update();
      },
      'summernote.dialog.shown': function () {
        self.hidePopover();
      },
    };

    self.initialize = function () {
      // create dialog markup
      const $container = options.dialogsInBody ? $(document.body) : context.layoutInfo.editor;

      const body = `${'<div class="form-group row-fluid">'
                + '<label>'}${lang.databasic.testLabel}</label>`
                + '<input class="ext-databasic-test form-control" type="text" />'
                + '</div>';
      const footer = `<button href="#" class="btn btn-primary ext-databasic-save">${lang.databasic.insert}</button>`;

      self.$dialog = ui.dialog({
        title: lang.databasic.name,
        fade: options.dialogsFade,
        body,
        footer,
      }).render().appendTo($container);

      // create popover
      self.$popover = ui.popover({
        className: 'ext-databasic-popover',
      }).render().appendTo('body');
      const $content = self.$popover.find('.popover-content');

      context.invoke('buttons.build', $content, options.popover.databasic);
    };

    self.destroy = function () {
      self.$popover.remove();
      self.$popover = null;
      self.$dialog.remove();
      self.$dialog = null;
    };

    self.update = function () {
      // Prevent focusing on editable when invoke('code') is executed
      if (!context.invoke('editor.hasFocus')) {
        self.hidePopover();
        return;
      }

      const rng = context.invoke('editor.createRange');
      let visible = false;

      if (rng.isOnData()) {
        const $data = $(rng.sc).closest('data.ext-databasic');

        if ($data.length) {
          const pos = dom.posFromPlaceholder($data[0]);

          self.$popover.css({
            display: 'block',
            left: pos.left,
            top: pos.top,
          });

          // save editor target to let size buttons resize the container
          context.invoke('editor.saveTarget', $data[0]);

          visible = true;
        }
      }

      // hide if not visible
      if (!visible) {
        self.hidePopover();
      }
    };

    self.hidePopover = function () {
      self.$popover.hide();
    };

    // define plugin dialog
    self.getInfo = function () {
      const rng = context.invoke('editor.createRange');

      if (rng.isOnData()) {
        const $data = $(rng.sc).closest('data.ext-databasic');

        if ($data.length) {
          // Get the first node on range(for edit).
          return {
            node: $data,
            test: $data.attr('data-test'),
          };
        }
      }

      return {};
    };

    self.setContent = function ($node) {
      $node.html(`<p contenteditable="false">${self.icon} ${lang.databasic.name}: ${
        $node.attr('data-test')}</p>`);
    };

    self.updateNode = function (info) {
      self.setContent(info.node
        .attr('data-test', info.test));
    };

    self.createNode = function (info) {
      const $node = $('<data class="ext-databasic"></data>');

      if ($node) {
        // save node to info structure
        info.node = $node;
        // insert node into editor dom
        context.invoke('editor.insertNode', $node[0]);
      }

      return $node;
    };

    self.showDialog = function () {
      const info = self.getInfo();
      const newNode = !info.node;
      context.invoke('editor.saveRange');

      self
        .openDialog(info)
        .then((dialogInfo) => {
          // [workaround] hide dialog before restore range for IE range focus
          ui.hideDialog(self.$dialog);
          context.invoke('editor.restoreRange');

          // insert a new node
          if (newNode) {
            self.createNode(info);
          }

          // update info with dialog info
          $.extend(info, dialogInfo);

          self.updateNode(info);
        })
        .fail(() => {
          context.invoke('editor.restoreRange');
        });
    };

    self.openDialog = function (info) {
      return $.Deferred((deferred) => {
        const $inpTest = self.$dialog.find('.ext-databasic-test');
        const $saveBtn = self.$dialog.find('.ext-databasic-save');
        const onKeyup = function (event) {
          if (event.keyCode === 13) {
            $saveBtn.trigger('click');
          }
        };

        ui.onDialogShown(self.$dialog, () => {
          context.triggerEvent('dialog.shown');

          $inpTest.val(info.test).on('input', () => {
            ui.toggleBtn($saveBtn, $inpTest.val());
          }).trigger('focus').on('keyup', onKeyup);

          $saveBtn
            .text(info.node ? lang.databasic.edit : lang.databasic.insert)
            .on('click', (event) => {
              event.preventDefault();

              deferred.resolve({ test: $inpTest.val() });
            });

          // init save button
          ui.toggleBtn($saveBtn, $inpTest.val());
        });

        ui.onDialogHidden(self.$dialog, () => {
          $inpTest.off('input keyup');
          $saveBtn.off('click');

          if (deferred.state() === 'pending') {
            deferred.reject();
          }
        });

        ui.showDialog(self.$dialog);
      });
    };
  };

  // Extends summernote
  $.extend(true, $.summernote, {
    plugins: {
      databasic: DataBasicPlugin,
    },

    options: {
      popover: {
        databasic: [
          ['databasic', ['databasicDialog', 'databasicSize100', 'databasicSize50', 'databasicSize25']],
        ],
      },
    },

    // add localization texts
    lang: {
      'en-US': {
        databasic: {
          name: 'Basic Data Container',
          insert: 'insert basic data container',
          edit: 'edit basic data container',
          testLabel: 'test input',
        },
      },
    },

  });
}));
