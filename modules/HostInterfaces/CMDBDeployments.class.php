<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBDeployments implements HostAPI
{

    public function getList()
    {
        $array = array('exec' => 'CMDBDeploymentListReturn');
        GlobalArgParser::setGlobalArgs($array);
        GlobalArgParser::setUser(CMDB_USER);
        GlobalArgParser::setPassword(CMDB_PASS);
        $output = QueryWrapper::execute('cmdbdeploymentlist');
        asort($output);
        return $output;
    }

    public function getInput()
    {
        return null;
    }

    public function getSearchResults($input)
    {
        $results = array();
        $param = $input->srchparam;
        $inputArray = array(
            'exec' => 'datareturner',
            'deployment' => $param,
            'rf' => array('config.fqdn', 'config.ipaddress', 'facts.ilo_ipaddress', 'cloud.rightscale.links.self')
        );
        GlobalArgParser::setGlobalArgs($inputArray);
        GlobalArgParser::setUser(CMDB_USER);
        GlobalArgParser::setPassword(CMDB_PASS);
        $output = QueryWrapper::execute(GlobalArgParser::getQueryLocation());
        foreach ($output as $idx => $hostObj) {
            if ((!isset($hostObj->fields)) || (empty($hostObj->fields))) {
                continue;
            }
            $fields = get_object_vars($hostObj->fields);
            if (empty($fields)) {
                continue;
            }
            $host = $fields['config.fqdn'];
            $results[$host]['host_name'] = $fields['config.fqdn'];
            $results[$host]['address'] = $fields['config.ipaddress'];
            if ((isset($fields['facts.ilo_ipaddress'])) && (!empty($fields['facts.ilo_ipaddress']))) {
                $results[$host]['action_url'] = 'https://' . $fields['facts.ilo_ipaddress'];
            } elseif ((isset($fields['cloud.rightscale.links.self'])) && (!empty($fields['cloud.rightscale.links.self']))) {
                $results[$host]['action_url'] = CMDBHelperFuncs::buildCloudHostUrl($fields['cloud.rightscale.links.self']);
            }
        }
        return $results;
    }

}

