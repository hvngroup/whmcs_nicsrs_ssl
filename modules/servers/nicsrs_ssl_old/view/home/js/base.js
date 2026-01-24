//Base JS
$(function() {
    $('.ctrl.value-picker i:first-child').click(function() {
        input = $(this).parent().find('input');
        value = input.val() * 1 - 1;
        if(input.attr('id') == 'extra_domain_count'){
            input.val(value > 0 ? value : 0);
        }else {
            input.val(value > 0 ? value : 1);
        }

        input.change();
    });
    $('.ctrl.value-picker i:last-child').click(function() {
        input = $(this).parent().find('input');
        maxValue = $(this).parent().attr('max') * 1;
        if(maxValue > 0 && input.val() * 1 > maxValue - 1) {
            value = maxValue - 1;
        } else {
            value = input.val();
        }
        input.val(value * 1 + 1);
        input.change();
    });
    $('.ctrl.value-picker-bar').each(function() {
        var bar = $(this);
        var picker = bar.find('.picker-bar i');
        var input = bar.find('input');

        var bs = bar.find('b');

        var setVal = function(val) {
            picker.css('margin-left', 32 * val - 8 + 'px');
            input.val(val);

            bs.removeClass('selected');
            for(var i = 0; i < val; i++) {
                $(bs[i]).addClass('selected');
            }
        }

        bs.each(function(index) {
            $(this).click(function() { setVal(index + 1); });
        });
        input.change(function() {
            setVal(input.val());
        });
    });
    $('.ctrl.order-list table, .ctrl.product-list table, .ctrl.product-notice').each(function() {
        var table = $(this);
        table.find('th input[type=checkbox]').click(function() {
            table.find('td input[type=checkbox]').prop('checked', this.checked);
            table.find('th input[type=checkbox]').prop('checked', this.checked);
        });
    });
    $('.ctrl.order-list').each(function() {
        var orderList = $(this);
        var table = $(this).find('table');
        $(this).find('.select-all-order').click(function() {
            table.find('input[type=checkbox]').prop('checked', true);
        });
    })
    $('.search-filter').each(function() {
        $(this).find('input.datepicker').datepicker();
    });
    window.PagerCtrl = function(element) {
        this.element = $(element);
        element = this.element;

        var lastPage = + element.find('.last-page').attr('page');

        element.find('input').keyup(function(event) {
            if (event.keyCode == 13) {
                var page = + $(this).val();
                page = page > lastPage ? lastPage : page;
                page = page > 1 ? page : 1;
                window.location.search = $.query.set('page', page);
            }
        });

        element.find('span').click(function() {
            window.location.hash = 'deal-list';
            window.location.search = $.query.set('page', $(this).attr('page'));
        });
    }
    new PagerCtrl('.ctrl.pager');
    
    $('.auto-renew-link').click(function() {
        var tr = $(this).parents('tr');
        var needRenew = !tr.hasClass('auto-renew');
        var itemId = tr.attr('itemId');
        $.get('/set-auto-renew-plesk', {
            'auto-renew': needRenew,
            'id': itemId
        }, function(res) {
            tr.toggleClass('auto-renew');
        });
    });
});