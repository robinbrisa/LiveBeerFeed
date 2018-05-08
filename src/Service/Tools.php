<?php
// src/Service/Tools.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User\User as User;
use App\Entity\Checkin\Checkin as Checkin;
use App\Entity\Brewery\Brewery as Brewery;
use App\Entity\Venue\Venue as Venue;
use App\Entity\Beer\Beer as Beer;
use App\Entity\Beer\Style as Style;
use Doctrine\ORM\EntityManager;

class Tools
{
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
    }
    
    public function getRatingImage($rating) {
        $rating = round(round($rating * 4) / 4, 2);
        if ($rating < 0) {
            $rating = 0;
        }
        if ($rating > 5) {
            $rating = 5;
        }
        $rating = $rating * 100;
        return '<span class="rating small r' . $rating . '"></span>';
    }
       
    public function getAPIKeysPool() {
        $usedKeys = $this->em->getRepository('App\Entity\APIQueryLog')->findUsedAPIKeys();
        $userKeys = $this->em->getRepository('App\Entity\User\User')->getAPIKeys();
        $finalPool = array_merge($userKeys, $usedKeys);
        arsort($finalPool);
        unset($usedKeys);
        unset($userKeys);
        return $finalPool;
    }
    
    public function getBestAPIKey($keyPool) {
        if ($keyPool['default'] > 1) {
            $APIToken = null;
        } else {
            unset($keyPool['default']);
            while (current($keyPool) < 1 && current($keyPool) !== false) {
                next($keyPool);
                if (current($keyPool) === false) {
                    return false;
                }
            }
            $APIToken = key($keyPool);
        }
        return $APIToken;
    }
    
    public function getEventBeersUserHasCheckedIn($user, $event) {        
        $sql = 'SELECT DISTINCT b.id ' .
        'FROM beer b ' .
        'JOIN checkin c ON c.beer_id = b.id ' .
        'JOIN user u ON c.user_id = u.id ' .
        'JOIN event_session_taplist tl ON c.beer_id = tl.beer_id ' .
        'JOIN event_session s ON tl.session_id = s.id ' .
        'JOIN event e ON s.event_id = e.id ' .
        'WHERE u.id = :userID ' .
        'AND e.id = :eventID';
        
        $query = $this->em->getConnection()->prepare($sql);
        $query->bindValue('userID', $user->getId());
        $query->bindValue('eventID', $event->getId());
        
        $query->execute();
        return $query->fetchAll();
    }
    
    public function countryCode($country) {
        $countrycodes = array (
            'Afghanistan' => 'AF',
            'Åland Islands' => 'AX',
            'Albania' => 'AL',
            'Algeria' => 'DZ',
            'American Samoa' => 'AS',
            'Andorra' => 'AD',
            'Angola' => 'AO',
            'Anguilla' => 'AI',
            'Antarctica' => 'AQ',
            'Antigua and Barbuda' => 'AG',
            'Argentina' => 'AR',
            'Australia' => 'AU',
            'Austria' => 'AT',
            'Azerbaijan' => 'AZ',
            'Bahamas' => 'BS',
            'Bahrain' => 'BH',
            'Bangladesh' => 'BD',
            'Barbados' => 'BB',
            'Belarus' => 'BY',
            'Belgium' => 'BE',
            'Belize' => 'BZ',
            'Benin' => 'BJ',
            'Bermuda' => 'BM',
            'Bhutan' => 'BT',
            'Bolivia' => 'BO',
            'Bosnia and Herzegovina' => 'BA',
            'Botswana' => 'BW',
            'Bouvet Island' => 'BV',
            'Brazil' => 'BR',
            'British Indian Ocean Territory' => 'IO',
            'Brunei Darussalam' => 'BN',
            'Bulgaria' => 'BG',
            'Burkina Faso' => 'BF',
            'Burundi' => 'BI',
            'Cambodia' => 'KH',
            'Cameroon' => 'CM',
            'Canada' => 'CA',
            'Cape Verde' => 'CV',
            'Cayman Islands' => 'KY',
            'Central African Republic' => 'CF',
            'Chad' => 'TD',
            'Chile' => 'CL',
            'China' => 'CN',
            'China / People\'s Republic of China' => 'CN',
            'Christmas Island' => 'CX',
            'Cocos (Keeling) Islands' => 'CC',
            'Colombia' => 'CO',
            'Comoros' => 'KM',
            'Congo' => 'CG',
            'Republic of Congo' => 'CG',
            'Zaire' => 'CD',
            'Cook Islands' => 'CK',
            'Costa Rica' => 'CR',
            'Côte D\'Ivoire' => 'CI',
            'Croatia' => 'HR',
            'Cuba' => 'CU',
            'Cyprus' => 'CY',
            'Czech Republic' => 'CZ',
            'Denmark' => 'DK',
            'Djibouti' => 'DJ',
            'Dominica' => 'DM',
            'Dominican Republic' => 'DO',
            'Ecuador' => 'EC',
            'Egypt' => 'EG',
            'El Salvador' => 'SV',
            'Equatorial Guinea' => 'GQ',
            'Eritrea' => 'ER',
            'Estonia' => 'EE',
            'Ethiopia' => 'ET',
            'Falkland Islands (Malvinas)' => 'FK',
            'Faroe Islands' => 'FO',
            'Fiji' => 'FJ',
            'Finland' => 'FI',
            'France' => 'FR',
            'French Guiana' => 'GF',
            'French Polynesia' => 'PF',
            'French Southern Territories' => 'TF',
            'Gabon' => 'GA',
            'Gambia' => 'GM',
            'Georgia' => 'GE',
            'Germany' => 'DE',
            'Ghana' => 'GH',
            'Gibraltar' => 'GI',
            'Greece' => 'GR',
            'Greenland' => 'GL',
            'Grenada' => 'GD',
            'Guadeloupe' => 'GP',
            'Guam' => 'GU',
            'Guatemala' => 'GT',
            'Guernsey' => 'GG',
            'Guinea' => 'GN',
            'Guinea-Bissau' => 'GW',
            'Guyana' => 'GY',
            'Haiti' => 'HT',
            'Heard Island and Mcdonald Islands' => 'HM',
            'Vatican City State' => 'VA',
            'Honduras' => 'HN',
            'Hong Kong' => 'HK',
            'Hungary' => 'HU',
            'Iceland' => 'IS',
            'India' => 'IN',
            'Indonesia' => 'ID',
            'Iran, Islamic Republic of' => 'IR',
            'Iraq' => 'IQ',
            'Ireland' => 'IE',
            'Isle of Man' => 'IM',
            'Israel' => 'IL',
            'Italy' => 'IT',
            'Jamaica' => 'JM',
            'Japan' => 'JP',
            'Jersey' => 'JE',
            'Jordan' => 'JO',
            'Kazakhstan' => 'KZ',
            'KENYA' => 'KE',
            'Kiribati' => 'KI',
            'Korea, Democratic People\'s Republic of' => 'KP',
            'Korea, Republic of' => 'KR',
            'Kuwait' => 'KW',
            'Kyrgyzstan' => 'KG',
            'Lao People\'s Democratic Republic' => 'LA',
            'Latvia' => 'LV',
            'Lebanon' => 'LB',
            'Lesotho' => 'LS',
            'Liberia' => 'LR',
            'Libyan Arab Jamahiriya' => 'LY',
            'Liechtenstein' => 'LI',
            'Lithuania' => 'LT',
            'Luxembourg' => 'LU',
            'Macao' => 'MO',
            'Macedonia, the Former Yugoslav Republic of' => 'MK',
            'Madagascar' => 'MG',
            'Malawi' => 'MW',
            'Malaysia' => 'MY',
            'Maldives' => 'MV',
            'Mali' => 'ML',
            'Malta' => 'MT',
            'Marshall Islands' => 'MH',
            'Martinique' => 'MQ',
            'Mauritania' => 'MR',
            'Mauritius' => 'MU',
            'Mayotte' => 'YT',
            'Mexico' => 'MX',
            'Micronesia, Federated States of' => 'FM',
            'Moldova, Republic of' => 'MD',
            'Monaco' => 'MC',
            'Mongolia' => 'MN',
            'Montenegro' => 'ME',
            'Montserrat' => 'MS',
            'Morocco' => 'MA',
            'Mozambique' => 'MZ',
            'Myanmar' => 'MM',
            'Namibia' => 'NA',
            'Nauru' => 'NR',
            'Nepal' => 'NP',
            'Netherlands' => 'NL',
            'Netherlands Antilles' => 'AN',
            'New Caledonia' => 'NC',
            'New Zealand' => 'NZ',
            'Nicaragua' => 'NI',
            'Niger' => 'NE',
            'Nigeria' => 'NG',
            'Niue' => 'NU',
            'Norfolk Island' => 'NF',
            'Northern Mariana Islands' => 'MP',
            'Norway' => 'NO',
            'Oman' => 'OM',
            'Pakistan' => 'PK',
            'Palau' => 'PW',
            'Palestinian Territory, Occupied' => 'PS',
            'Panama' => 'PA',
            'Papua New Guinea' => 'PG',
            'Paraguay' => 'PY',
            'Peru' => 'PE',
            'Philippines' => 'PH',
            'Pitcairn' => 'PN',
            'Poland' => 'PL',
            'Portugal' => 'PT',
            'Puerto Rico' => 'PR',
            'Qatar' => 'QA',
            'Réunion' => 'RE',
            'Romania' => 'RO',
            'Russian Federation' => 'RU',
            'Russia' => 'RU',
            'Rwanda' => 'RW',
            'Saint Helena' => 'SH',
            'Saint Kitts and Nevis' => 'KN',
            'Saint Lucia' => 'LC',
            'Saint Pierre and Miquelon' => 'PM',
            'Saint Vincent and the Grenadines' => 'VC',
            'Samoa' => 'WS',
            'San Marino' => 'SM',
            'Sao Tome and Principe' => 'ST',
            'Saudi Arabia' => 'SA',
            'Senegal' => 'SN',
            'Serbia' => 'RS',
            'Seychelles' => 'SC',
            'Sierra Leone' => 'SL',
            'Singapore' => 'SG',
            'Slovakia' => 'SK',
            'Slovenia' => 'SI',
            'Solomon Islands' => 'SB',
            'Somalia' => 'SO',
            'South Africa' => 'ZA',
            'South Georgia and the South Sandwich Islands' => 'GS',
            'Spain' => 'ES',
            'Sri Lanka' => 'LK',
            'Sudan' => 'SD',
            'Suriname' => 'SR',
            'Svalbard and Jan Mayen' => 'SJ',
            'Swaziland' => 'SZ',
            'Sweden' => 'SE',
            'Switzerland' => 'CH',
            'Syrian Arab Republic' => 'SY',
            'Taiwan, Province of China' => 'TW',
            'Tajikistan' => 'TJ',
            'Tanzania, United Republic of' => 'TZ',
            'Thailand' => 'TH',
            'Timor-Leste' => 'TL',
            'Togo' => 'TG',
            'Tokelau' => 'TK',
            'Tonga' => 'TO',
            'Trinidad and Tobago' => 'TT',
            'Tunisia' => 'TN',
            'Turkey' => 'TR',
            'Turkmenistan' => 'TM',
            'Turks and Caicos Islands' => 'TC',
            'Tuvalu' => 'TV',
            'Uganda' => 'UG',
            'Ukraine' => 'UA',
            'United Arab Emirates' => 'AE',
            'United Kingdom' => 'GB',
            'Northern Ireland' => 'GB',
            'England' => 'GB',
            'Scotland' => 'GB',
            'Wales' => 'GB',
            'United States' => 'US',
            'United States Minor Outlying Islands' => 'UM',
            'Uruguay' => 'UY',
            'Uzbekistan' => 'UZ',
            'Vanuatu' => 'VU',
            'Venezuela' => 'VE',
            'Viet Nam' => 'VN',
            'Virgin Islands, British' => 'VG',
            'Virgin Islands, U.S.' => 'VI',
            'Wallis and Futuna' => 'WF',
            'Western Sahara' => 'EH',
            'Yemen' => 'YE',
            'Zambia' => 'ZM',
            'Zimbabwe' => 'ZW',
        );
        if (array_key_exists($country, $countrycodes)) {
            return $this->code2unicode($countrycodes[$country]);
        } else {
            return "";
        }
    }
    
    private function enclosedUnicode($char) {
        $arr = array(
            'a' => '1F1E6',
            'b' => '1F1E7',
            'c' => '1F1E8',
            'd' => '1F1E9',
            'e' => '1F1EA',
            'f' => '1F1EB',
            'g' => '1F1EC',
            'h' => '1F1ED',
            'i' => '1F1EE',
            'j' => '1F1EF',
            'k' => '1F1F0',
            'l' => '1F1F1',
            'm' => '1F1F2',
            'n' => '1F1F3',
            'o' => '1F1F4',
            'p' => '1F1F5',
            'q' => '1F1F6',
            'r' => '1F1F7',
            's' => '1F1F8',
            't' => '1F1F9',
            'u' => '1F1FA',
            'v' => '1F1FB',
            'w' => '1F1FC',
            'x' => '1F1FD',
            'y' => '1F1FE',
            'z' => '1F1FF',
        );
        $char = strtolower($char);
        if (array_key_exists($char, $arr)) {
            return mb_convert_encoding('&#x'.$arr[$char].';', 'UTF-8', 'HTML-ENTITIES');
        }
        throw new Exception('Invalid character '.$char);
    }
    
    private function code2unicode($code) {
        $arr = str_split($code);
        $str = '';
        foreach ($arr as $char) {
            $str .= $this->enclosedUnicode($char);
        }
        return $str;
    }
}