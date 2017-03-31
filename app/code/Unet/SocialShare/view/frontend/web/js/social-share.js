/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define(["jquery"], function ($) {
    "use strict";
    return function socialShare(data) {
        var socialShare = {
            config: data.config,
            currentStoreUrl: data.currentStoreUrl,
            shareBlock: '#shareto .share-list',
            shareButton: '#shareto .button',
            itemClass: 'share-icon',
            iconType: 'square',
            width: 800,
            height: 600,

            /**
             * Initialize
             */
            init: function() {
                this.click();
                this.render(this.config, this.currentStoreUrl);
                this.openPopup();
            },

            /**
             * TODO
             * Click action
             */
            click: function () {
                $(this.shareButton).on('click', function () {
                    $(this.shareBlock).toggle();
                }.bind(this));
            },

            /**
             * render share items
             * @param config
             * @param currentStoreUrl
             */
            render: function (config, currentStoreUrl) {
                var $this = this;
                var shareBlock = $(this.shareBlock);
                for (var key in config) {
                    if (config.hasOwnProperty(key)) {
                        var shareItem = $('<li>', {class: $this.itemClass});
                        var shareLink = $('<a>');
                        var shareIcon = $('<i>', {class: 'icon ' + key});
                        var href = '';
                        switch (key) {
                            case 'facebook':
                                href = config[key] + '?u=' + url + '&title=' + $this.title;
                                break;
                            case 'twitter':
                                href = config[key] + '?text=' + $this.title + '&url=' + url;
                                break;
                            case 'google-plus':
                                href = config[key] + '?url=' + url;
                                break;
                            case 'pinterest':
                                href = config[key] + '?url=' + url + '&description=' + $this.title;
                                break;
                        }

                        // render item
                        shareLink.attr('href', href);
                        shareLink.append(shareIcon);
                        shareItem.append(shareLink);
                        shareBlock.append(shareItem);
                    }
                }
            },

            /**
             * Open share page in new window
             */
            openPopup: function () {
                var $this = this;
                $(this.shareBlock).find('.' + this.itemClass).click(function () {
                    var newWindow = window.open($(this).find('a').prop('href'), '', 'height=' + $this.height + ',width=' + $this.width + '');
                    if (window.focus) {
                        newWindow.focus();
                    }
                    return false;
                });
            }
        };

        $(document).ready(function () {
            socialShare.init();
        });
    }
});