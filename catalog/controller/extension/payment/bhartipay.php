<?php
/**
 * @package     OpenCart
 * @author      BhartiPay
 * @copyright   Copyright (c) 2018, BhartiPay Services Pvt Ltd.
 * @license     https://opensource.org/licenses/GPL-3.0
 * @link        https://www.bhartipay.com
 */

class ControllerExtensionPaymentBhartiPay extends Controller
{

    /**
     * HTML entity decode
     * @param  string $string string
     * @return string         formatted output
     */
    public function __($string)
    {
        return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    }

    public function index()
    {
        require_once(DIR_SYSTEM . 'bppg_helper.php');
        if (!$this->config->get('payment_bhartipay_test')) {
            $data['action'] = 'https://merchant.bhartipay.com/crm/jsp/paymentrequest';
        } else {
            $data['action'] = 'http://uat.bhartipay.com/crm/jsp/paymentrequest';
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $return_url = $this->url->link('extension/payment/bhartipay/callback', 'language=' . $this->config->get('config_language') . '&hash=' . md5($order_info['order_id'] . $order_info['total'] . $order_info['currency_code'] . $this->config->get('payment_bhartipay_salt')));

        $transaction_request = new BPPGModule();

        /* Setting all values here */
        $transaction_request->setPayId($this->config->get('payment_bhartipay_pay_id'));
        $transaction_request->setPgRequestUrl($data['action']);
        $transaction_request->setSalt($this->config->get('payment_bhartipay_salt'));
        $transaction_request->setReturnUrl($return_url);
        $transaction_request->setCurrencyCode(356);
        $transaction_request->setTxnType('SALE');
        $transaction_request->setOrderId($order_info['order_id']);
        $transaction_request->setCustEmail($order_info['email']);
        $transaction_request->setCustName($this->__($order_info['payment_firstname']) . ' ' . $this->__($order_info['payment_lastname']));
        $transaction_request->setCustStreetAddress1($this->__($order_info['payment_address_1']));
        $transaction_request->setCustCity($this->__($order_info['payment_city']));
        $transaction_request->setCustState($this->__($order_info['payment_zone']));
        $transaction_request->setCustCountry($this->__($order_info['payment_iso_code_3']));
        $transaction_request->setCustZip($this->__($order_info['payment_postcode']));
        $transaction_request->setCustPhone($order_info['telephone']);
        $transaction_request->setAmount($order_info['total'] * 100); // convert to Rupee from Paisa
        $transaction_request->setProductDesc($order_info['telephone']);
        $transaction_request->setCustShipStreetAddress1($this->__($order_info['shipping_address_1']));
        $transaction_request->setCustShipCity($this->__($order_info['shipping_city']));
        $transaction_request->setCustShipState($this->__($order_info['shipping_zone']));
        $transaction_request->setCustShipCountry($this->__($order_info['shipping_iso_code_3']));
        $transaction_request->setCustShipZip($this->__($order_info['shipping_postcode']));
        $transaction_request->setCustShipPhone($order_info['telephone']);
        $transaction_request->setCustShipName($this->__($order_info['shipping_firstname']).' '.$this->__($order_info['shipping_lastname']));

        // Generate postdata and redirect form
        $postdata = $transaction_request->createTransactionRequest();
        $postdata['action_url'] = $data['action'];
        // echo "<pre>";var_dump($postdata);die();
        return $this->load->view('extension/payment/bhartipay', $postdata);
    }

    public function callback()
    {
        $this->load->language('extension/payment/bhartipay');

        if (isset($this->request->post['ORDER_ID'])) {
            $order_id = $this->request->post['ORDER_ID'];
        } else {
            $order_id = 0;
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            $error = '';

            if (!isset($this->request->post['RESPONSE_CODE']) || !isset($this->request->get['hash'])) {
                $error = $this->language->get('text_unable');
            } elseif ($this->request->post['STATUS'] != 'Captured') {
                $error = $this->language->get('text_declined');
            } elseif ($this->request->get['hash'] != md5($order_info['order_id'] . $order_info['total'] . $order_info['currency_code'] . $this->config->get('payment_bhartipay_salt'))) {
                $error = $this->language->get('text_unable');
            }
        } else {
            $error = $this->language->get('text_unable');
        }

        if ($error) {
            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_basket'),
                'href' => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_checkout'),
                'href' => $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'))
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_failed'),
                'href' => $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'))
            );

            $data['text_message'] = sprintf($this->language->get('text_failed_message'), $error, $this->url->link('information/contact', 'language=' . $this->config->get('config_language')));

            $data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('common/success', $data));
        } else {
            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_bhartipay_order_status_id'));

            $this->response->redirect($this->url->link('checkout/success', 'language=' . $this->config->get('config_language')));
        }
    }
}
