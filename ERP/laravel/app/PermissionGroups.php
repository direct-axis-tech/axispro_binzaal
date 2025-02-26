<?php

namespace App;

use ArrayAccess;
use Exception;

class PermissionGroups implements ArrayAccess {
    /** @var string SS_SADMIN System administration */
    const SS_SADMIN = 1<<8;

    /** @var string SS_SETUP Company setup */
    const SS_SETUP = 2<<8;

    /** @var string SS_SPEC Special maintenance */
    const SS_SPEC = 3<<8;

    /** @var string SS_SALES_C Sales configuration */
    const SS_SALES_C = 11<<8;

    /** @var string SS_SALES Sales transactions */
    const SS_SALES = 12<<8;

    /** @var string SS_SALES_A Sales related reports */
    const SS_SALES_A = 13<<8;

    /** @var string SS_PURCH_C Purchase configuration */
    const SS_PURCH_C = 21<<8;

    /** @var string SS_PURCH Purchase transactions */
    const SS_PURCH = 22<<8;

    /** @var string SS_PURCH_A Purchase analytics */
    const SS_PURCH_A = 23<<8;

    /** @var string SS_ITEMS_C Inventory configuration */
    const SS_ITEMS_C = 31<<8;

    /** @var string SS_ITEMS Inventory operations */
    const SS_ITEMS = 32<<8;

    /** @var string SS_ITEMS_A Inventory analytics */
    const SS_ITEMS_A = 33<<8;

    /** @var string SS_ASSETS_C Fixed Assets configuration */
    const SS_ASSETS_C = 36<<8;

    /** @var string SS_ASSETS Fixed Assets operations */
    const SS_ASSETS = 37<<8;

    /** @var string SS_ASSETS_A Fixed Assets analytics */
    const SS_ASSETS_A = 38<<8;

    /** @var string SS_MANUF_C Manufacturing configuration */
    const SS_MANUF_C = 41<<8;

    /** @var string SS_MANUF Manufacturing transactions */
    const SS_MANUF = 42<<8;

    /** @var string SS_MANUF_A Manufacturing analytics */
    const SS_MANUF_A = 43<<8;

    /** @var string SS_DIM_C Dimensions configuration */
    const SS_DIM_C = 51<<8;

    /** @var string SS_DIM Dimensions */
    const SS_DIM = 52<<8;
    
    /** @var string SS_DIM Dimensions Analytics */
    const SS_DIM_A = 53<<8;

    /** @var string SS_GL_C Banking & GL configuration */
    const SS_GL_C = 61<<8;

    /** @var string SS_GL Banking & GL transactions */
    const SS_GL = 62<<8;

    /** @var string SS_GL_A Banking & GL analytics */
    const SS_GL_A = 63<<8;
    
    /** @var string SS_HRM_A HRMS Analytics */
    const SS_HRM_A = 71<<8;
    
    /** @var string SS_HRM_C HRM Configurations */
    const SS_HRM_C = 72<<8;
    
    /** @var string SS_HRM HRM Transactions */
    const SS_HRM = 73<<8;

    /** @var string SS_HEAD_MENU Header menu display permission */
    const SS_HEAD_MENU = 81<<8;

    /** @var string SS_FINANCE Finance related */
    const SS_FINANCE = 91<<8;

    /** @var string SS_FINANCE_A Finance analytics */
    const SS_FINANCE_A = 92<<8;

    /** @var string SS_CRM_A Customer Relationship Analytics */
    const SS_CRM_A = 101<<8;

    /** @var string SS_DASHBOARD Dashboard Sections */
    const SS_DASHBOARD = 102<<8;

    /** @var string SS_LABOUR Labour Transactions */
    const SS_LABOUR = 111<<8;

    /** @var string SS_LABOUR Labour Analytics */
    const SS_LABOUR_A = 112<<8;

    /** @var string SS_LABOUR Labour Analytics */
    const SS_LABOUR_C = 113<<8;

    private $sections = [];

    public function __construct()
    {
        $this->sections = [
            self::SS_SADMIN => trans("System administration"),
            self::SS_SETUP => trans("Company setup"),
            self::SS_SPEC => trans("Special maintenance"),
            self::SS_SALES_C => trans("Sales configuration"),
            self::SS_SALES => trans("Sales transactions"),
            self::SS_SALES_A => trans("Sales related reports"),
            self::SS_PURCH_C => trans("Purchase configuration"),
            self::SS_PURCH => trans("Purchase transactions"),
            self::SS_PURCH_A => trans("Purchase analytics"),
            self::SS_ITEMS_C => trans("Inventory configuration"),
            self::SS_ITEMS => trans("Inventory operations"),
            self::SS_ITEMS_A => trans("Inventory analytics"),
            self::SS_ASSETS_C => trans("Fixed Assets configuration"),
            self::SS_ASSETS => trans("Fixed Assets operations"),
            self::SS_ASSETS_A => trans("Fixed Assets analytics"),
            self::SS_MANUF_C => trans("Manufacturing configuration"),
            self::SS_MANUF => trans("Manufacturing transactions"),
            self::SS_MANUF_A => trans("Manufacturing analytics"),
            self::SS_DIM_C => trans("Dimensions configuration"),
            self::SS_DIM => trans("Dimensions"),
            self::SS_DIM_A => trans("Dimensions Analytics"),
            self::SS_GL_C => trans("Banking & GL configuration"),
            self::SS_GL => trans("Banking & GL transactions"),
            self::SS_GL_A => trans("Banking & GL analytics"),
            self::SS_HEAD_MENU => trans("Header menu display permission"),
            self::SS_FINANCE => trans("Finance related"),
            self::SS_FINANCE_A => trans("Finance analytics"),
            self::SS_CRM_A => trans("Customer Relationship Analytics"),
            self::SS_DASHBOARD => trans("Dashboard Sections"),
            self::SS_HRM => trans("HRM Transactions"),
            self::SS_HRM_A => trans("HRMS Analytics"),
            self::SS_HRM_C => trans("HRM Configurations"),
            self::SS_LABOUR => trans("Labour Transactions"),
            self::SS_LABOUR_A => trans("Labour Analytics"),
            self::SS_LABOUR_C => trans("Labour Configuration"),
        ];
    }

    /**
     * Returns the array containing the permission groups
     *
     * @return array
     */
    public function toArray() {
        return $this->sections;
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->sections[$key]);
    }

    /**
     * Get the value at a given offset.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->sections[$key];
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     * 
     * @throws Exception
     */
    public function offsetSet($key, $value)
    {
        // we don't allow to set value directly
        throw new Exception("Trying to overwrite system permission group directly");
    }

    /**
     * Unset the value at a given offset.
     *
     * @param  string  $key
     * @return void
     * 
     * @throws Exception
     */
    public function offsetUnset($key)
    {
        // we don't allow to unset a value either
        throw new Exception("Trying to overwrite system permission group directly");
    }

}