splitit_paymentmethod
======================

Splitit payment gateway for Magento2


Install
=======

1. Download .zip file, extract the .zip file.

2. Go to Magento2/app/code/ folder (if there is no "code" folder than create the same )

3. Create Splitit/Paymentmethod

4. Put all the extracted folders and files.

5. Run the commands:

    php bin/magento setup:upgrade (run this command in root of magento 2.0)
	php bin/magento setup:static-content:deploy (run this command in root of magento 2.0)
	php bin/magento cache:clean (run this command)

6. Enable and configure Splitit in Magento Admin under Stores/Configuration/Payment Methods/Splitit


