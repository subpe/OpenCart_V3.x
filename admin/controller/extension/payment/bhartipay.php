<?php
class ControllerExtensionPaymentBhartiPay extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/payment/bhartipay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_bhartipay', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment'));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['pay_id'])) {
            $data['error_pay_id'] = $this->error['pay_id'];
        } else {
            $data['error_pay_id'] = '';
        }

        if (isset($this->error['salt'])) {
            $data['error_salt'] = $this->error['salt'];
        } else {
            $data['error_salt'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/bhartipay', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action'] = $this->url->link('extension/payment/bhartipay', 'user_token=' . $this->session->data['user_token']);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

        if (isset($this->request->post['payment_bhartipay_pay_id'])) {
            $data['payment_bhartipay_pay_id'] = $this->request->post['payment_bhartipay_pay_id'];
        } else {
            $data['payment_bhartipay_pay_id'] = $this->config->get('payment_bhartipay_pay_id');
        }

        if (isset($this->request->post['payment_bhartipay_salt'])) {
            $data['payment_bhartipay_pay_id'] = $this->request->post['payment_bhartipay_salt'];
        } else {
            $data['payment_bhartipay_salt'] = $this->config->get('payment_bhartipay_salt');
        }

        if (isset($this->request->post['payment_bhartipay_test'])) {
            $data['payment_bhartipay_test'] = $this->request->post['payment_bhartipay_test'];
        } else {
            $data['payment_bhartipay_test'] = $this->config->get('payment_bhartipay_test');
        }

        if (isset($this->request->post['payment_bhartipay_total'])) {
            $data['payment_bhartipay_total'] = $this->request->post['payment_bhartipay_total'];
        } else {
            $data['payment_bhartipay_total'] = $this->config->get('payment_bhartipay_total');
        }

        if (isset($this->request->post['payment_bhartipay_order_status_id'])) {
            $data['payment_bhartipay_order_status_id'] = $this->request->post['payment_bhartipay_order_status_id'];
        } else {
            $data['payment_bhartipay_order_status_id'] = $this->config->get('payment_bhartipay_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_bhartipay_geo_zone_id'])) {
            $data['payment_bhartipay_geo_zone_id'] = $this->request->post['payment_bhartipay_geo_zone_id'];
        } else {
            $data['payment_bhartipay_geo_zone_id'] = $this->config->get('payment_bhartipay_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_bhartipay_status'])) {
            $data['payment_bhartipay_status'] = $this->request->post['payment_bhartipay_status'];
        } else {
            $data['payment_bhartipay_status'] = $this->config->get('payment_bhartipay_status');
        }

        if (isset($this->request->post['payment_bhartipay_sort_order'])) {
            $data['payment_bhartipay_sort_order'] = $this->request->post['payment_bhartipay_sort_order'];
        } else {
            $data['payment_bhartipay_sort_order'] = $this->config->get('payment_bhartipay_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/bhartipay', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/bhartipay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_bhartipay_pay_id']) {
            $this->error['pay_id'] = $this->language->get('error_pay_id');
        }

        if (!$this->request->post['payment_bhartipay_salt']) {
            $this->error['salt'] = $this->language->get('error_salt');
        }

        return !$this->error;
    }
}
