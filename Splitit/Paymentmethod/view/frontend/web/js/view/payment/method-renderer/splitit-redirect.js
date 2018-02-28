/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Splitit_Paymentmethod/js/action/set-payment-method-action',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        quote,
        customerData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Splitit_Paymentmethod/payment/splitit-redirect-form',
                billingAgreement: ''
            },

            /** Init observable variables */
            initObservable: function () {
                console.log("splitit-redirect-method-renderer");
                this._super()
                    .observe('billingAgreement');

                return this;
            },

            /** Open window with  */
            showAcceptanceWindow: function (data, event) {
                window.open(
                    $(event.target).attr('href'),
                    'olcwhatissplitit',
                    'toolbar=no, location=no,' +
                    ' directories=no, status=no,' +
                    ' menubar=no, scrollbars=yes,' +
                    ' resizable=yes, ,left=0,' +
                    ' top=0, width=400, height=350'
                );

                return false;
            },

            /** Returns payment acceptance mark link path */
            getPaymentAcceptanceMarkHref: function () {
                return window.checkoutConfig.payment.splititExpress.paymentAcceptanceMarkHref;
            },

            /** Returns payment acceptance mark image path */
            getPaymentAcceptanceMarkSrc: function () {
                return window.checkoutConfig.payment.splititExpress.paymentAcceptanceMarkSrc;
            },

            /** Returns billing agreement data */
            getBillingAgreementCode: function () {
                return window.checkoutConfig.payment.splititExpress.billingAgreementCode[this.item.method];
            },

            /** Returns payment information data */
            getData: function () {
                var parent = this._super(),
                    additionalData = null;

                if (this.getBillingAgreementCode()) {
                    additionalData = {};
                    additionalData[this.getBillingAgreementCode()] = this.billingAgreement();
                }

                return $.extend(true, parent, {
                    'additional_data': additionalData
                });
            },

            /** Redirect to splitit */
            continueToSplitit: function () {
                console.log("window.checkoutConfig.payment.splititExpress.redirectUrl["+quote.paymentMethod().method+"]==");
                console.log(window.checkoutConfig.payment.splititExpress.redirectUrl[quote.paymentMethod().method]);
                if (additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.mage.redirect(
                                window.checkoutConfig.payment.splititExpress.redirectUrl[quote.paymentMethod().method]
                            );
                        }
                    );

                    return false;
                }
            }
        });
    }
);