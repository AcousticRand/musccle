<?php
/**
 * Author: nhughart
 * Date: 1/29/13
 */ 
class Lyonscg_Musccle_Block_Adminhtml_Cache_Header extends Mage_Core_Block_Template {

    const XML_PATH_MUSCCLE_SERVERS      =      "musccle/servers";

    public function getServers()
    {
        return array('127.0.0.1','127.0.0.1');
    }
}