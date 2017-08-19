<?php

namespace Achmadfatoni\Poslaju;

use GuzzleHttp\Client;

class Poslaju
{
    /**
     * Track poslaju parcel
     *
     * @param $trackingNumber
     * @return array
     */
    public function track($trackingNumber)
    {
        $url = "http://poslaju.com.my/track-trace-v2/"; # url of poslaju tracking website
        # store post data into array (poslaju website only receive the tracking no with POST, not GET. So we need to POST data)
        $client = new Client();
        $res = $client->request('POST', $url, [
            'form_params' => [
                'trackingNo03' => $trackingNumber,
                'hvtrackNoHeader03' => '',
                'hvfromheader03' => 0,
            ]
        ]);

        $httpstatus = $res->getStatusCode();
        $result = $res->getBody();

        # using regex (regular expression) to parse the HTML webpage.
        # we only want to good stuff
        # regex patern
        $patern = "#<table id='tbDetails'(.*?)</table>#";
        # execute regex
        preg_match_all($patern, $result, $parsed);
        # parse the table by row <tr>
        $trpatern = "#<tr>(.*?)</tr>#";
        preg_match_all($trpatern, implode('', $parsed[0]), $tr);
        unset($tr[0][0]); # remove an array element because we don't need the 1st row (<th></th>)
        $tr[0] = array_values($tr[0]); # rearrange the array index

        # array for keeping the data
        $trackResult = array();
        $trackResult['http_code'] = $httpstatus; # set http response code into the array
        # checking if record found or not, by checking the number of rows available in the result table
        if (count($tr[0]) > 0) {
            # record found, so proceed
            # iterate through the array, access the data needed and store into new array
            for ($i = 0; $i < count($tr[0]); $i++) {
                # parse the table by column <td>
                $tdpatern = "#<td>(.*?)</td>#";
                preg_match_all($tdpatern, $tr[0][$i], $td);

                # store into variable, strip_tags is for removeing html tags
                $datetime = strip_tags($td[0][0]);
                $process = strip_tags($td[0][1]);
                $event = strip_tags($td[0][2]);
                # store into associative array
                $datetime = explode(',', $datetime);
                $trackResult['data'][$i]['date'] = $datetime[0];
                $trackResult['data'][$i]['time'] = $datetime[1];
                $trackResult['data'][$i]['process'] = $process;
                $trackResult['data'][$i]['event'] = $event;
            }
        } else {
            $trackResult['message'] = 'Parcel not found';
        }

        return $trackResult;
    }

}