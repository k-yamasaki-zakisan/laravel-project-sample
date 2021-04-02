(function IIFE() {

    "use strict";

    function factory($) {
        var pluginName = "searcher",
            dataKey = "plugin_" + pluginName,
            defaults = {
                itemSelector: "tbody > tr",
                textSelector: "td",
                inputSelector: "",
                caseSensitive: false,
                toggle: function (item, containsText) {
                    $(item).toggle(containsText);
                }
            };

        function Searcher(element, options) {
            this.element = element;

            this.options = $.extend({}, defaults, options);

            this._create();
        }

        Searcher.prototype = {
            dispose: function () {
                this._$input.unbind("." + pluginName);
                var options = this.options,
                    toggle = options.toggle || defaults.toggle;
                this._$element.find(options.itemSelector).each(function () { toggle(this, true); });
            },
            filter: function (value) {
                this._lastValue = value;

                var options = this.options,
                    textSelector = options.textSelector,
                    toggle = options.toggle || defaults.toggle;

                var flags = "gm" + (!options.caseSensitive ? "i" : "");
                var regex = new RegExp("(" + escapeRegExp(value) + ")", flags);

                this._$element
                    .find(options.itemSelector)
                    .each(function eachItem() {
                        var $item = $(this),
                            $textElements = textSelector ? $item.find(textSelector) : $item,
                            itemContainsText = false;

                        $textElements = $textElements.each(function eachTextElement() {
                            itemContainsText = itemContainsText || !!$(this).text().match(regex);
                            return !itemContainsText;
                        });

                        toggle(this, itemContainsText);
                    });
            },
            _create: function () {
                var options = this.options;

                this._$element = $(this.element);

                this._fn = $.proxy(this._onValueChange, this);
                var eventNames = "input." + pluginName + " change." + pluginName + " keyup." + pluginName;
                this._$input = $(options.inputSelector).bind(eventNames, this._fn);

                this._lastValue = null;

                var toggle = options.toggle || defaults.toggle;
                this._$element.find(options.itemSelector).each(function () { toggle(this, true); });
            },
            _onValueChange: function () {
                var value = this._$input.val();
                if (value === this._lastValue)
                    return;

                this.filter(value);
            }
        };

        function escapeRegExp(text) {
            return text.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
        }

        $.fn[pluginName] = function pluginHandler(options) {
            var args = Array.prototype.slice.call(arguments, 1);
            return this.each(function () {
                var searcher = $.data(this, dataKey);
                var t = typeof (options);
                if (t === "string" && searcher) {
                    searcher[options].apply(searcher, args);
                    if (options === "dispose")
                        $.removeData(this, dataKey);
                }
                else if (t === "object") {
                    if (!searcher)
                        $.data(this, dataKey, new Searcher(this, options));
                    else
                        $.extend(searcher.options, options);
                }
            });
        };

    }

    if (typeof (define) === "function" && define.amd)
        define(["jquery"], factory);
    else if (typeof (exports) === "object")
        module.exports = factory;
    else
        factory(jQuery);

}).call(this);