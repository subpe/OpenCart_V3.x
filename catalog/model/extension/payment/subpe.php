<?php
/**
 * @package     OpenCart
 * @author      SubPe
 * @copyright   Copyright (c) 2018, SubPe Services Pvt Ltd.
 * @license     https://opensource.org/licenses/GPL-3.0
 * @link        https://www.subpe.in
 */

/**
 * Model for SubPe module
 */
class ModelExtensionPaymentSubPe extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/subpe');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_subpe_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('payment_subpe_total') > 0 && $this->config->get('payment_subpe_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_subpe_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $currencies = array(
            'AUD',
            'USD',
            'INR',
        );

        if (!in_array(strtoupper($this->session->data['currency']), $currencies)) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'subpe',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_subpe_sort_order')
            );
        }

        return $method_data;
    }
}
