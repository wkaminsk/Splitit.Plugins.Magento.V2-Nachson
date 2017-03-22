/**
 * Splitit_Payment Magento JS component
 *
 * @category    Splitit
 * @package     Splitit_Payment
 * @author      Ivan Weiler & Stjepan Udovičić
 * @copyright   Splitit (http://Splitit.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Splitit_Paymentmethod/payment/splitit-form'
            },

            getCode: function() {
                return 'splitit_paymentmethod';
            },

            isActive: function() {
                return true;
            },

            getFixedAmount: function() {
                return window.checkoutConfig.payment.splitit_payment.fixedamount;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }
        });
    }
);
