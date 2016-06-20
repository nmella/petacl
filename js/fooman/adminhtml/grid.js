/**
* @author     Kristof Ringleff
* @package    Fooman_OrderManager
* @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
var varienGridMassaction = Class.create(varienGridMassaction, {
    apply: function ($super) {

        //carrier choices
        var carrierChoices = [];
        $('sales_order_grid_table').getElementsBySelector('.fooman_carrier').each(function (s) {
            carrierChoices.push(s.readAttribute('rel') + '|' + s.value);
        });
        new Insertion.Bottom(this.formAdditional, this.fieldTemplate.evaluate({
            name: 'carrier',
            value: carrierChoices
        }));

        //tracking numbers choices
        var trackingNumbers = [];
        $('sales_order_grid_table').getElementsBySelector('.fooman_tracking').each(function (s) {
            trackingNumbers.push(s.readAttribute('rel') + '|' + s.value);
        });
        new Insertion.Bottom(this.formAdditional, this.fieldTemplate.evaluate({
            name: 'tracking',
            value: trackingNumbers
        }));

        return $super();
    },
    onGridRowClick: function ($super, grid, evt) {
        var tdElement = Event.findElement(evt, 'td');
        if ($(tdElement).down('textarea')) {
            Event.stop(event);
            return;
        }

        return $super(grid, evt);
    }
});

var varienGrid = Class.create(varienGrid, {
    doFilter: function ($super) {
        var asked = false;
        $('sales_order_grid_table').getElementsBySelector('.fooman_tracking_numbers').each(function (s) {
            if (s.value && !asked) {
                asked = true;
                if (window.confirm('If you continue you will lose the already entered tracking numbers.')) {
                    return $super();
                } else {
                    return false;
                }
            }
        });
        if (!asked) {
            return $super();
        }
    }
});
