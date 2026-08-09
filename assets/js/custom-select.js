function getOptionContent($option) {
  var flag = $option.attr('data-flag') || '';
  var text = $option.text();
  if (flag) return '<img src="' + flag + '" alt="" style="width:18px;height:12px;vertical-align:middle;margin-inline-end:6px;border-radius:2px;object-fit:cover;">' + text;
  return text;
}

function initCustomSelects() {
    $('select[data-custom-select]:not(.no-custom)').each(function () {
        const $this = $(this);

        if ($this.parent().hasClass('custom-select-wrapper')) {
            return;
        }

        const selectId = $this.attr('id') || Math.random().toString(36).substr(2, 9);
        $this.attr('data-custom-id', selectId);

        const options = $this.find('option');
        const $wrapper = $('<div class="custom-select-wrapper"></div>');
        const $customSelect = $('<div class="custom-select"></div>');
        const $trigger = $('<div class="custom-select-trigger"></div>');
        const $optionsContainer = $('<div class="custom-options"></div>');

        $this.addClass('select-hidden');
        $this.wrap($wrapper);
        $this.after($customSelect);

        $optionsContainer.attr('data-for', selectId);
        $('body').append($optionsContainer);

        const $selected = options.filter(':selected');
        const $first = options.first();
        $trigger.html($selected.length ? getOptionContent($selected) : getOptionContent($first));
        $customSelect.append($trigger);

        const hasSearch = options.length > 7;
        if (hasSearch) {
            const $searchBox = $('<div class="custom-select-search-box" style="padding:8px 10px;border-bottom:1px solid rgba(255,255,255,0.08);background:#121214;position:sticky;top:0;z-index:10"><input type="text" placeholder="بحث..." class="custom-select-search-input" style="width:100%;padding:6px 10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:6px;color:#fff;font-size:0.85rem;text-align:start;outline:none;font-family:inherit;"></div>');
            $optionsContainer.append($searchBox);

            $searchBox.find('input').on('click', function(e) {
                e.stopPropagation();
            }).on('input', function(e) {
                const query = $(this).val().toLowerCase().trim();
                $optionsContainer.find('.custom-option').each(function() {
                    const text = $(this).text().toLowerCase();
                    const keywords = ($(this).attr('data-search-keywords') || '').toLowerCase();
                    if (text.indexOf(query) > -1 || keywords.indexOf(query) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        }

        options.each(function () {
            const $option = $(this);
            const $customOption = $('<div class="custom-option"></div>')
                .html(getOptionContent($option))
                .attr('data-value', $option.val())
                .attr('data-search-keywords', $option.attr('data-search-keywords') || '');

            if ($option.is(':selected')) {
                $customOption.addClass('selection');
            }

            $optionsContainer.append($customOption);
        });

        $trigger.on('click', function (e) {
            e.stopPropagation();
            const isOpen = $customSelect.hasClass('open');

            $('.custom-select').removeClass('open');
            $('.custom-options').not($optionsContainer).removeClass('open-options');

            if (!isOpen) {
                $customSelect.addClass('open');
                $optionsContainer.addClass('open-options');

                const $currentSearch = $optionsContainer.find('.custom-select-search-input');
                if ($currentSearch.length > 0) {
                    $currentSearch.val('').trigger('input');
                }

                updateOptionsPosition();

                if ($currentSearch.length > 0) {
                    setTimeout(() => $currentSearch.focus(), 50);
                }
            } else {
                $customSelect.removeClass('open');
                $optionsContainer.removeClass('open-options');
            }
        });

        function updateOptionsPosition() {
            if ($customSelect.hasClass('open')) {
                const rect = $trigger[0].getBoundingClientRect();
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;

                $optionsContainer.css({ visibility: 'hidden', display: 'block', width: 'max-content' });
                const dropdownWidth = $optionsContainer.outerWidth();
                const dropdownHeight = $optionsContainer.outerHeight();
                $optionsContainer.css({ visibility: '', display: '' });

                let cssProps = {
                    position: 'fixed',
                    zIndex: 2147483647,
                    minWidth: rect.width + 'px',
                    width: 'max-content',
                    maxWidth: '200px',
                    left: rect.left + 'px',
                    right: 'auto'
                };

                if (rect.bottom + dropdownHeight > windowHeight && rect.top > dropdownHeight) {
                    cssProps.top = (rect.top - dropdownHeight - 5) + 'px';
                } else {
                    cssProps.top = (rect.bottom + 5) + 'px';
                }

                if (rect.left + rect.width > windowWidth) {
                    cssProps.left = 'auto';
                    cssProps.right = (windowWidth - rect.right) + 'px';
                }

                $optionsContainer.css(cssProps);
            }
        }

        let selectTicking = false;
        function handleSelectScroll() {
            if (!selectTicking) {
                window.requestAnimationFrame(function () {
                    updateOptionsPosition();
                    selectTicking = false;
                });
                selectTicking = true;
            }
        }

        $(window).on('scroll resize', handleSelectScroll);
        $('.header, .full_view').on('scroll', handleSelectScroll);

        $optionsContainer.on('click', '.custom-option', function (e) {
            e.stopPropagation();
            const val = $(this).attr('data-value');
            const html = $(this).html();

            $this.val(val).trigger('change');
            $trigger.html(html);

            $(this).addClass('selection').siblings().removeClass('selection');
            $customSelect.removeClass('open');
            $optionsContainer.removeClass('open-options');
        });
    });
}

$(document).on('click', function () {
    $('.custom-select').removeClass('open');
    $('.custom-options').removeClass('open-options');
});

window.updateCustomSelect = function($select) {
    const selectId = $select.attr('data-custom-id');
    if (!selectId) return;

    const $optionsContainer = $(`.custom-options[data-for="${selectId}"]`);
    const $trigger = $select.siblings('.custom-select').find('.custom-select-trigger');
    const options = $select.find('option');

    $optionsContainer.empty();
    let selectedHtml = '';

    const hasSearch = options.length > 7;
    if (hasSearch) {
        const $searchBox = $('<div class="custom-select-search-box" style="padding:8px 10px;border-bottom:1px solid rgba(255,255,255,0.08);background:#121214;position:sticky;top:0;z-index:10"><input type="text" placeholder="بحث..." class="custom-select-search-input" style="width:100%;padding:6px 10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:6px;color:#fff;font-size:0.85rem;text-align:right;outline:none;font-family:inherit;"></div>');
        $optionsContainer.append($searchBox);

        $searchBox.find('input').on('click', function(e) {
            e.stopPropagation();
        }).on('input', function(e) {
            const query = $(this).val().toLowerCase().trim();
            $optionsContainer.find('.custom-option').each(function() {
                const text = $(this).text().toLowerCase();
                const keywords = ($(this).attr('data-search-keywords') || '').toLowerCase();
                if (text.indexOf(query) > -1 || keywords.indexOf(query) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

    options.each(function () {
        const $option = $(this);
        const content = getOptionContent($option);
        const $customOption = $('<div class="custom-option"></div>')
            .html(content)
            .attr('data-value', $option.val())
            .attr('data-search-keywords', $option.attr('data-search-keywords') || '');

        if ($option.is(':selected')) {
            $customOption.addClass('selection');
            selectedHtml = content;
        }

        $optionsContainer.append($customOption);
    });

    if (!selectedHtml) selectedHtml = getOptionContent(options.first());
    $trigger.html(selectedHtml);
};

$(document).ready(function () {
    if (typeof i18n !== 'undefined') i18n.translateDOM();
    initCustomSelects();

    $(document).on('change', 'select[data-custom-select]', function() {
        if (window.updateCustomSelect) {
            window.updateCustomSelect($(this));
        }
    });
});
