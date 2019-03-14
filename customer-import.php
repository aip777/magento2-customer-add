<?php

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$websiteId = $storeManager->getStore()->getWebsiteId();

$firstName = 'Mr.Kamal';
$lastName = 'Islam';
$email = 'kamal@example.com';
$password = 'khan123';
$gender = 1;

$address = array(
    'customer_address_id' => '',
    'prefix' => '',
    'firstname' => $firstName,
    'middlename' => '',
    'lastname' => $lastName,
    'suffix' => '',
    'company' => 'ZeroZeroSoft',
    'street' => array(
        '0' => 'Customer Address 1', // this is mandatory
        '1' => 'Customer Address 2' // this is optional
    ),
    'city' => 'New York',
    'country_id' => 'US', // two letters country code
    'region' => 'New York', // can be empty '' if no region
    'region_id' => '43', // can be empty '' if no region_id
    'postcode' => '10450',
    'telephone' => '123-456-7890',
    'fax' => '',
    'save_in_address_book' => 1,
    'gender' => $gender,
);

$customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();

/**
 * check whether the email address is already registered or not
 */
$customer = $customerFactory->setWebsiteId($websiteId)->loadByEmail($email);

/**
 * if email address already registered, return the error message
 * else, create new customer account
 */
if ($customer->getId()) {
    echo 'Customer with email '.$email.' is already registered.';
} else {
    try {
        $customer = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
        $customer->setWebsiteId($websiteId);
        $customer->setEmail($email);
        $customer->setFirstname($firstName);
        $customer->setLastname($lastName);
        $customer->setPassword($password);
        $customer->setGender($gender);
        $customer->setGender($gender);

        // save customer
        $customer->save();

        $customer->setConfirmation(null);
        $customer->save();

        $customAddress = $objectManager->get('\Magento\Customer\Model\AddressFactory')->create();
        $customAddress->setData($address)
            ->setCustomerId($customer->getId())
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1');

        // save customer address
        $customAddress->save();

        //reindex customer grid index
        $indexerFactory = $objectManager->get('Magento\Indexer\Model\IndexerFactory');
        $indexerId = 'customer_grid';
        $indexer = $indexerFactory->create();
        $indexer->load($indexerId);
        $indexer->reindexAll();

        // Create customer session
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $customerSession->setCustomerAsLoggedIn($customer);

        echo 'Customer with email '.$email.' is successfully created.';

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}