/**
 * Splitit_Paymentmethod Magento JS component
 *
 * @category    Splitit
 * @package     Splitit_Paymentmethod
 * @author      Ivan Weiler & Stjepan Udovičić
 * @copyright   Splitit (http://Splitit.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'splitit_paymentredirect',
                component: 'Splitit_Paymentmethod/js/view/payment/method-renderer/splitit-redirect'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);