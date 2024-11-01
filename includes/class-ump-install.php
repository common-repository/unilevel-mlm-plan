<?php
defined('ABSPATH') || exit;
/**
 * BMP_Install Class.
 */
class UMP_Install
{
    private static $background_updater;
    /**
     * Hook in tabs.
     */
    public static function init()
    {
        add_filter('plugin_action_links_' . UMP_PLUGIN_BASENAME, array(__CLASS__, 'plugin_action_links'));
    }

    public static function install()
    {
        if (!is_blog_installed()) {
            return;
        }
        self::create_tables();
        self::create_roles();
        self::insert_table_data();
        self::create_pages();
    }
    private static function create_tables()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $wpdb->hide_errors();
        $get_tables = self::get_schema();
        foreach ($get_tables as $get_table) {
            dbDelta($get_table);
        }
    }

    public static function get_schema()
    {
        global $wpdb;
        $tables = array();
        $collate = '';

        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $tables[] = "CREATE TABLE {$wpdb->prefix}ump_users 
        (id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        user_id BIGINT(20) NOT NULL COMMENT 'foreign key of the {$wpdb->prefix}users table',
        user_name VARCHAR(60) NOT NULL ,
        user_key VARCHAR(15) NOT NULL ,
        parent_key VARCHAR(15) NOT NULL ,
        sponsor_key VARCHAR(15) NOT NULL ,
        payment_status ENUM('0','1','2' ) NOT NULL DEFAULT '0' COMMENT ' 0 indicate free Epin AND 1 indicate paid and 2 Indicate None Paid Member',
        level int(5) NOT NULL DEFAULT '0',
        status int(1) NOT NULL DEFAULT '0',
        payment_date VARCHAR(255),
        next_payment_date VARCHAR(255),
        KEY index_user_key (user_key),
        KEY index_parent_key (parent_key),
        KEY index_sponsor_key (sponsor_key),
        UNIQUE (user_name)
        ) $collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ump_country (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        iso CHAR(2) NOT NULL,
        name VARCHAR(80) NOT NULL,
        iso3 CHAR(3) DEFAULT NULL,
        numcode SMALLINT(6) DEFAULT NULL
        ) $collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ump_currency (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        iso3 VARCHAR (5) NOT NULL,
        symbol VARCHAR (50) NULL,
        currency VARCHAR( 60 ) NOT NULL
        ) $collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ump_epins (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        price int NOT NULL,
        epin_no  VARCHAR(60) NOT NULL,
        type  VARCHAR(60) NOT NULL,
        date_generated  datetime NOT NULL,
        user_key  VARCHAR(15) NOT NULL DEFAULT 0,
        date_used  datetime NOT NULL,
        status boolean NOT NULL DEFAULT 0
        ) $collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ump_hierarchy
        (id int(20) unsigned NOT NULL AUTO_INCREMENT primary key,
        parent_id VARCHAR(15) NOT NULL,
        child_id VARCHAR(15) NOT NULL,
        level int(11) NOT NULL,
        bonus_status int(11) NOT NULL DEFAULT '0',
        KEY parent_id (parent_id),
        KEY level (level)
        ) $collate;";
        return $tables;
    }


    public static function get_tables()
    {
        global $wpdb;

        $tables = array(
            "{$wpdb->prefix}ump_users",
            "{$wpdb->prefix}ump_country",
            "{$wpdb->prefix}ump_currency",
            "{$wpdb->prefix}ump_hierarchy",
            "{$wpdb->prefix}ump_epins"
        );

        $tables = apply_filters('ump_install_get_tables', $tables);

        return $tables;
    }
    /**
     * Create roles and capabilities.
     */
    public static function create_roles()
    {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        add_role('ump_user', 'Unilevel MLM Plan', array('read' => false,));

        $capabilities = self::get_core_capabilities();

        foreach ($capabilities as $cap_group) {
            foreach ($cap_group as $cap) {
                $wp_roles->add_cap('ump_user', $cap);
                $wp_roles->add_cap('administrator', $cap);
            }
        }
    }

    private static function get_core_capabilities()
    {
        $capabilities = array();

        $capabilities['core'] = array(
            'manage_ump',
        );
        return $capabilities;
    }


    public static function plugin_action_links($links)
    {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=ump-settings') . '" aria-label="' . esc_attr__('View Unilevel MLM Plan settings', 'ump') . '">' . esc_html__('Settings', 'ump') . '</a>',
        );

        return array_merge($action_links, $links);
    }


    public static function insert_table_data()
    {
        global $wpdb;

        $insert = "INSERT INTO {$wpdb->prefix}ump_country (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES
				(1, 'AF', 'Afghanistan', 'AFG', 4),
				(2, 'AL', 'Albania', 'ALB', 8),
				(3, 'DZ', 'Algeria', 'DZA', 12),
				(4, 'AS', 'American Samoa', 'ASM', 16),
				(5, 'AD', 'Andorra', 'AND', 20),
				(6, 'AO', 'Angola', 'AGO', 24),
				(7, 'AI', 'Anguilla', 'AIA', 660),
				(8, 'AQ', 'Antarctica', NULL, NULL),
				(9, 'AG', 'Antigua and Barbuda', 'ATG', 28),
				(10, 'AR', 'Argentina', 'ARG', 32),
				(11, 'AM', 'Armenia', 'ARM', 51),
				(12, 'AW', 'Aruba', 'ABW', 533),
				(13, 'AU', 'Australia', 'AUS', 36),
				(14, 'AT', 'Austria', 'AUT', 40),
				(15, 'AZ', 'Azerbaijan', 'AZE', 31),
				(16, 'BS', 'Bahamas', 'BHS', 44),
				(17, 'BH', 'Bahrain', 'BHR', 48),
				(18, 'BD', 'Bangladesh', 'BGD', 50),
				(19, 'BB', 'Barbados', 'BRB', 52),
				(20, 'BY', 'Belarus', 'BLR', 112),
				(21, 'BE', 'Belgium', 'BEL', 56),
				(22, 'BZ', 'Belize', 'BLZ', 84),
				(23, 'BJ', 'Benin', 'BEN', 204),
				(24, 'BM', 'Bermuda', 'BMU', 60),
				(25, 'BT', 'Bhutan', 'BTN', 64),
				(26, 'BO', 'Bolivia', 'BOL', 68),
				(27, 'BA', 'Bosnia and Herzegovina', 'BIH', 70),
				(28, 'BW', 'Botswana', 'BWA', 72),
				(29, 'BV', 'Bouvet Island', NULL, NULL),
				(30, 'BR', 'Brazil', 'BRA', 76),
				(31, 'IO', 'British Indian Ocean Territory', NULL, NULL),
				(32, 'BN', 'Brunei Darussalam', 'BRN', 96),
				(33, 'BG', 'Bulgaria', 'BGR', 100),
				(34, 'BF', 'Burkina Faso', 'BFA', 854),
				(35, 'BI', 'Burundi', 'BDI', 108),
				(36, 'KH', 'Cambodia', 'KHM', 116),
				(37, 'CM', 'Cameroon', 'CMR', 120),
				(38, 'CA', 'Canada', 'CAN', 124),
				(39, 'CV', 'Cape Verde', 'CPV', 132),
				(40, 'KY', 'Cayman Islands', 'CYM', 136),
				(41, 'CF', 'Central African Republic', 'CAF', 140),
				(42, 'TD', 'Chad', 'TCD', 148),
				(43, 'CL', 'Chile', 'CHL', 152),
				(44, 'CN', 'China', 'CHN', 156),
				(45, 'CX', 'Christmas Island', NULL, NULL),
				(46, 'CC', 'Cocos (Keeling) Islands', NULL, NULL),
				(47, 'CO', 'Colombia', 'COL', 170),
				(48, 'KM', 'Comoros', 'COM', 174),
				(49, 'CG', 'Congo', 'COG', 178),
				(50, 'CD', 'Congo, the Democratic Republic of the', 'COD', 180),
				(51, 'CK', 'Cook Islands', 'COK', 184),
				(52, 'CR', 'Costa Rica', 'CRI', 188),
				(53, 'CI', 'Cote D''Ivoire', 'CIV', 384),
				(54, 'HR', 'Croatia', 'HRV', 191),
				(55, 'CU', 'Cuba', 'CUB', 192),
				(56, 'CY', 'Cyprus', 'CYP', 196),
				(57, 'CZ', 'Czech Republic', 'CZE', 203),
				(58, 'DK', 'Denmark', 'DNK', 208),
				(59, 'DJ', 'Djibouti', 'DJI', 262),
				(60, 'DM', 'Dominica', 'DMA', 212),
				(61, 'DO', 'Dominican Republic', 'DOM', 214),
				(62, 'EC', 'Ecuador', 'ECU', 218),
				(63, 'EG', 'Egypt', 'EGY', 818),
				(64, 'SV', 'El Salvador', 'SLV', 222),
				(65, 'GQ', 'Equatorial Guinea', 'GNQ', 226),
				(66, 'ER', 'Eritrea', 'ERI', 232),
				(67, 'EE', 'Estonia', 'EST', 233),
				(68, 'ET', 'Ethiopia', 'ETH', 231),
				(69, 'FK', 'Falkland Islands (Malvinas)', 'FLK', 238),
				(70, 'FO', 'Faroe Islands', 'FRO', 234),
				(71, 'FJ', 'Fiji', 'FJI', 242),
				(72, 'FI', 'Finland', 'FIN', 246),
				(73, 'FR', 'France', 'FRA', 250),
				(74, 'GF', 'French Guiana', 'GUF', 254),
				(75, 'PF', 'French Polynesia', 'PYF', 258),
				(76, 'TF', 'French Southern Territories', NULL, NULL),
				(77, 'GA', 'Gabon', 'GAB', 266),
				(78, 'GM', 'Gambia', 'GMB', 270),
				(79, 'GE', 'Georgia', 'GEO', 268),
				(80, 'DE', 'Germany', 'DEU', 276),
				(81, 'GH', 'Ghana', 'GHA', 288),
				(82, 'GI', 'Gibraltar', 'GIB', 292),
				(83, 'GR', 'Greece', 'GRC', 300),
				(84, 'GL', 'Greenland', 'GRL', 304),
				(85, 'GD', 'Grenada', 'GRD', 308),
				(86, 'GP', 'Guadeloupe', 'GLP', 312),
				(87, 'GU', 'Guam', 'GUM', 316),
				(88, 'GT', 'Guatemala', 'GTM', 320),
				(89, 'GN', 'Guinea', 'GIN', 324),
				(90, 'GW', 'Guinea-Bissau', 'GNB', 624),
				(91, 'GY', 'Guyana', 'GUY', 328),
				(92, 'HT', 'Haiti', 'HTI', 332),
				(93, 'HM', 'Heard Island and Mcdonald Islands', NULL, NULL),
				(94, 'VA', 'Holy See (Vatican City State)', 'VAT', 336),
				(95, 'HN', 'Honduras', 'HND', 340),
				(96, 'HK', 'Hong Kong', 'HKG', 344),
				(97, 'HU', 'Hungary', 'HUN', 348),
				(98, 'IS', 'Iceland', 'ISL', 352),
				(99, 'IN', 'India', 'IND', 356),
				(100, 'ID', 'Indonesia', 'IDN', 360),
				(101, 'IR', 'Iran, Islamic Republic of', 'IRN', 364),
				(102, 'IQ', 'Iraq', 'IRQ', 368),
				(103, 'IE', 'Ireland', 'IRL', 372),
				(104, 'IL', 'Israel', 'ISR', 376),
				(105, 'IT', 'Italy', 'ITA', 380),
				(106, 'JM', 'Jamaica', 'JAM', 388),
				(107, 'JP', 'Japan', 'JPN', 392),
				(108, 'JO', 'Jordan', 'JOR', 400),
				(109, 'KZ', 'Kazakhstan', 'KAZ', 398),
				(110, 'KE', 'Kenya', 'KEN', 404),
				(111, 'KI', 'Kiribati', 'KIR', 296),
				(112, 'KP', 'Korea, Democratic People''s Republic of', 'PRK', 408),
				(113, 'KR', 'Korea, Republic of', 'KOR', 410),
				(114, 'KW', 'Kuwait', 'KWT', 414),
				(115, 'KG', 'Kyrgyzstan', 'KGZ', 417),
				(116, 'LA', 'Lao People''s Democratic Republic', 'LAO', 418),
				(117, 'LV', 'Latvia', 'LVA', 428),
				(118, 'LB', 'Lebanon', 'LBN', 422),
				(119, 'LS', 'Lesotho', 'LSO', 426),
				(120, 'LR', 'Liberia', 'LBR', 430),
				(121, 'LY', 'Libyan Arab Jamahiriya', 'LBY', 434),
				(122, 'LI', 'Liechtenstein', 'LIE', 438),
				(123, 'LT', 'Lithuania', 'LTU', 440),
				(124, 'LU', 'Luxembourg', 'LUX', 442),
				(125, 'MO', 'Macao', 'MAC', 446),
				(126, 'MK', 'Macedonia, the Former Yugoslav Republic of', 'MKD', 807),
				(127, 'MG', 'Madagascar', 'MDG', 450),
				(128, 'MW', 'Malawi', 'MWI', 454),
				(129, 'MY', 'Malaysia', 'MYS', 458),
				(130, 'MV', 'Maldives', 'MDV', 462),
				(131, 'ML', 'Mali', 'MLI', 466),
				(132, 'MT', 'Malta', 'MLT', 470),
				(133, 'MH', 'Marshall Islands', 'MHL', 584),
				(134, 'MQ', 'Martinique', 'MTQ', 474),
				(135, 'MR', 'Mauritania', 'MRT', 478),
				(136, 'MU', 'Mauritius', 'MUS', 480),
				(137, 'YT', 'Mayotte', NULL, NULL),
				(138, 'MX', 'Mexico', 'MEX', 484),
				(139, 'FM', 'Micronesia, Federated States of', 'FSM', 583),
				(140, 'MD', 'Moldova, Republic of', 'MDA', 498),
				(141, 'MC', 'Monaco', 'MCO', 492),
				(142, 'MN', 'Mongolia', 'MNG', 496),
				(143, 'MS', 'Montserrat', 'MSR', 500),
				(144, 'MA', 'Morocco', 'MAR', 504),
				(145, 'MZ', 'Mozambique', 'MOZ', 508),
				(146, 'MM', 'Myanmar', 'MMR', 104),
				(147, 'NA', 'Namibia', 'NAM', 516),
				(148, 'NR', 'Nauru', 'NRU', 520),
				(149, 'NP', 'Nepal', 'NPL', 524),
				(150, 'NL', 'Netherlands', 'NLD', 528),
				(151, 'AN', 'Netherlands Antilles', 'ANT', 530),
				(152, 'NC', 'New Caledonia', 'NCL', 540),
				(153, 'NZ', 'New Zealand', 'NZL', 554),
				(154, 'NI', 'Nicaragua', 'NIC', 558),
				(155, 'NE', 'Niger', 'NER', 562),
				(156, 'NG', 'Nigeria', 'NGA', 566),
				(157, 'NU', 'Niue', 'NIU', 570),
				(158, 'NF', 'Norfolk Island', 'NFK', 574),
				(159, 'MP', 'Northern Mariana Islands', 'MNP', 580),
				(160, 'NO', 'Norway', 'NOR', 578),
				(161, 'OM', 'Oman', 'OMN', 512),
				(162, 'PK', 'Pakistan', 'PAK', 586),
				(163, 'PW', 'Palau', 'PLW', 585),
				(164, 'PS', 'Palestinian Territory, Occupied', NULL, NULL),
				(165, 'PA', 'Panama', 'PAN', 591),
				(166, 'PG', 'Papua New Guinea', 'PNG', 598),
				(167, 'PY', 'Paraguay', 'PRY', 600),
				(168, 'PE', 'Peru', 'PER', 604),
				(169, 'PH', 'Philippines', 'PHL', 608),
				(170, 'PN', 'Pitcairn', 'PCN', 612),
				(171, 'PL', 'Poland', 'POL', 616),
				(172, 'PT', 'Portugal', 'PRT', 620),
				(173, 'PR', 'Puerto Rico', 'PRI', 630),
				(174, 'QA', 'Qatar', 'QAT', 634),
				(175, 'RE', 'Reunion', 'REU', 638),
				(176, 'RO', 'Romania', 'ROM', 642),
				(177, 'RU', 'Russian Federation', 'RUS', 643),
				(178, 'RW', 'Rwanda', 'RWA', 646),
				(179, 'SH', 'Saint Helena', 'SHN', 654),
				(180, 'KN', 'Saint Kitts and Nevis', 'KNA', 659),
				(181, 'LC', 'Saint Lucia', 'LCA', 662),
				(182, 'PM', 'Saint Pierre and Miquelon', 'SPM', 666),
				(183, 'VC', 'Saint Vincent and the Grenadines', 'VCT', 670),
				(184, 'WS', 'Samoa', 'WSM', 882),
				(185, 'SM', 'San Marino', 'SMR', 674),
				(186, 'ST', 'Sao Tome and Principe', 'STP', 678),
				(187, 'SA', 'Saudi Arabia', 'SAU', 682),
				(188, 'SN', 'Senegal', 'SEN', 686),
				(189, 'CS', 'Serbia and Montenegro', NULL, NULL),
				(190, 'SC', 'Seychelles', 'SYC', 690),
				(191, 'SL', 'Sierra Leone', 'SLE', 694),
				(192, 'SG', 'Singapore', 'SGP', 702),
				(193, 'SK', 'Slovakia', 'SVK', 703),
				(194, 'SI', 'Slovenia', 'SVN', 705),
				(195, 'SB', 'Solomon Islands', 'SLB', 90),
				(196, 'SO', 'Somalia', 'SOM', 706),
				(197, 'ZA', 'South Africa', 'ZAF', 710),
				(198, 'GS', 'South Georgia and the South Sandwich Islands', NULL, NULL),
				(199, 'ES', 'Spain', 'ESP', 724),
				(200, 'LK', 'Sri Lanka', 'LKA', 144),
				(201, 'SD', 'Sudan', 'SDN', 736),
				(202, 'SR', 'Suriname', 'SUR', 740),
				(203, 'SJ', 'Svalbard and Jan Mayen', 'SJM', 744),
				(204, 'SZ', 'Swaziland', 'SWZ', 748),
				(205, 'SE', 'Sweden', 'SWE', 752),
				(206, 'CH', 'Switzerland', 'CHE', 756),
				(207, 'SY', 'Syrian Arab Republic', 'SYR', 760),
				(208, 'TW', 'Taiwan, Province of China', 'TWN', 158),
				(209, 'TJ', 'Tajikistan', 'TJK', 762),
				(210, 'TZ', 'Tanzania, United Republic of', 'TZA', 834),
				(211, 'TH', 'Thailand', 'THA', 764),
				(212, 'TL', 'Timor-Leste', NULL, NULL),
				(213, 'TG', 'Togo', 'TGO', 768),
				(214, 'TK', 'Tokelau', 'TKL', 772),
				(215, 'TO', 'Tonga', 'TON', 776),
				(216, 'TT', 'Trinidad and Tobago', 'TTO', 780),
				(217, 'TN', 'Tunisia', 'TUN', 788),
				(218, 'TR', 'Turkey', 'TUR', 792),
				(219, 'TM', 'Turkmenistan', 'TKM', 795),
				(220, 'TC', 'Turks and Caicos Islands', 'TCA', 796),
				(221, 'TV', 'Tuvalu', 'TUV', 798),
				(222, 'UG', 'Uganda', 'UGA', 800),
				(223, 'UA', 'Ukraine', 'UKR', 804),
				(224, 'AE', 'United Arab Emirates', 'ARE', 784),
				(225, 'GB', 'United Kingdom', 'GBR', 826),
				(226, 'US', 'United States', 'USA', 840),
				(227, 'UM', 'United States Minor Outlying Islands', NULL, NULL),
				(228, 'UY', 'Uruguay', 'URY', 858),
				(229, 'UZ', 'Uzbekistan', 'UZB', 860),
				(230, 'VU', 'Vanuatu', 'VUT', 548),
				(231, 'VE', 'Venezuela', 'VEN', 862),
				(232, 'VN', 'Viet Nam', 'VNM', 704),
				(233, 'VG', 'Virgin Islands, British', 'VGB', 92),
				(234, 'VI', 'Virgin Islands, U.s.', 'VIR', 850),
				(235, 'WF', 'Wallis and Futuna', 'WLF', 876),
				(236, 'EH', 'Western Sahara', 'ESH', 732),
				(237, 'YE', 'Yemen', 'YEM', 887),
				(238, 'ZM', 'Zambia', 'ZMB', 894),
				(239, 'ZW', 'Zimbabwe', 'ZWE', 716)";

        $results = $wpdb->get_results("select * from {$wpdb->prefix}bmp_country");
        $rows = $wpdb->num_rows;
        if (empty($rows) || $rows < 239) {
            $wpdb->query($insert);
        }


        $insert = "INSERT INTO {$wpdb->prefix}ump_currency (`id`, `iso3`, `symbol`, `currency`) VALUES
				(1, 'AED', '', 'Emirati Dirham'),
				(2, 'AFN', '؋', 'Afghan Afghani'),
				(3, 'ALL', 'Lek', 'Albanian Lek'),
				(4, 'AMD', '', 'Armenian Dram'),
				(5, 'ANG', 'ƒ', 'Dutch Guilder'),
				(6, 'AOA', '', 'Angolan Kwanza'),
				(7, 'ARS', '$', 'Argentine Peso'),
				(8, 'AUD', '$', 'Australian Dollar'),
				(9, 'AWG', 'ƒ', 'Aruban or Dutch Guilder'),
				(10, 'AZN', '₼', 'Azerbaijani New Manat'),
				(11, 'BAM', 'KM', 'Bosnian Convertible Marka'),
				(12, 'BBD', '$', 'Barbadian or Bajan Dollar'),
				(13, 'BDT', '', 'Bangladeshi Taka'),
				(14, 'BGN', 'лв', 'Bulgarian Lev'),
				(15, 'BHD', '', 'Bahraini Dinar'),
				(16, 'BIF', '', 'Burundian Franc'),
				(17, 'BMD', '$', 'Bermudian Dollar'),
				(18, 'BND', '$', 'Bruneian Dollar'),
				(19, 'BOB', 'Bs', 'Bolivian Boliviano'),
				(20, 'BRL', 'R$', 'Brazilian Real'),
				(21, 'BSD', '$', 'Bahamian Dollar'),
				(22, 'BTN', '', 'Bhutanese Ngultrum'),
				(23, 'BWP', 'P', 'Botswana Pula'),
				(24, 'BYR', 'Br', 'Belarusian Ruble'),
				(25, 'BZD', 'BZ$', 'Belizean Dollar'),
				(26, 'CAD', '$', 'Canadian Dollar'),
				(27, 'CDF', '', 'Congolese Franc'),
				(28, 'CHF', 'CHF', 'Swiss Franc'),
				(29, 'CLP', '$', 'Chilean Peso'),
				(30, 'CNY', '¥', 'Chinese Yuan Renminbi'),
				(31, 'COP', '$', 'Colombian Peso'),
				(32, 'CRC', '₡', 'Costa Rican Colon'),
				(33, 'CUC', '', 'Cuban Convertible Peso'),
				(34, 'CUP', '₱', 'Cuban Peso'),
				(35, 'CVE', '', 'Cape Verdean Escudo'),
				(36, 'CZK', 'Kč', 'Czech Koruna'),
				(37, 'DJF', '', 'Djiboutian Franc'),
				(38, 'DKK', 'kr', 'Danish Krone'),
				(39, 'DOP', 'RD$', 'Dominican Peso'),
				(40, 'DZD', '', 'Algerian Dinar'),
				(41, 'EGP', '£', 'Egyptian Pound'),
				(42, 'ERN', '', 'Eritrean Nakfa'),
				(43, 'ETB', '', 'Ethiopian Birr'),
				(44, 'EUR', '€', 'Euro'),
				(45, 'FJD', '$', 'Fijian Dollar'),
				(46, 'FKP', '£', 'Falkland Island Pound'),
				(47, 'GBP', '£', 'British Pound'),
				(48, 'GEL', '', 'Georgian Lari'),
				(49, 'GGP', '£', 'Guernsey Pound'),
				(50, 'GHS', '¢', 'Ghanaian Cedi'),
				(51, 'GIP', '£', 'Gibraltar Pound'),
				(52, 'GMD', '', 'Gambian Dalasi'),
				(53, 'GNF', '', 'Guinean Franc'),
				(54, 'GTQ', 'Q', 'Guatemalan Quetzal'),
				(55, 'GYD', '$', 'Guyanese Dollar'),
				(56, 'HKD', '$', 'Hong Kong Dollar'),
				(57, 'HNL', 'L', 'Honduran Lempira'),
				(58, 'HRK', 'kn', 'Croatian Kuna'),
				(59, 'HTG', '', 'Haitian Gourde'),
				(60, 'HUF', 'Ft', 'Hungarian Forint'),
				(61, 'IDR', 'Rp', 'Indonesian Rupiah'),
				(62, 'ILS', '₪', 'Israeli Shekel'),
				(63, 'IMP', '£', 'Isle of Man Pound'),
				(64, 'INR', '₹', 'Indian Rupee'),
				(65, 'IQD', '', 'Iraqi Dinar'),
				(66, 'IRR', '﷼', 'Iranian Rial'),
				(67, 'ISK', 'kr', 'Icelandic Krona'),
				(68, 'JEP', '£', 'Jersey Pound'),
				(69, 'JMD', 'J$', 'Jamaican Dollar'),
				(70, 'JOD', '', 'Jordanian Dinar'),
				(71, 'JPY', '¥', 'Japanese Yen'),
				(72, 'KES', '', 'Kenyan Shilling'),
				(73, 'KGS', 'лв', 'Kyrgyzstani Som'),
				(74, 'KHR', '៛', 'Cambodian Riel'),
				(75, 'KMF', '', 'Comoran Franc'),
				(76, 'KPW', '₩', 'North Korean Won'),
				(77, 'KRW', '₩', 'South Korean Won'),
				(78, 'KWD', '', 'Kuwaiti Dinar'),
				(79, 'KYD', '$', 'Caymanian Dollar'),
				(80, 'KZT', 'лв', 'Kazakhstani Tenge'),
				(81, 'LAK', '₭', 'Lao or Laotian Kip'),
				(82, 'LBP', '£', 'Lebanese Pound'),
				(83, 'LKR', '₨', 'Sri Lankan Rupee'),
				(84, 'LRD', '$', 'Liberian Dollar'),
				(85, 'LSL', '', 'Basotho Loti'),
				(86, 'LTL', '', 'Lithuanian Litas'),
				(87, 'LVL', '', 'Latvian Lat'),
				(88, 'LYD', '', 'Libyan Dinar'),
				(89, 'MAD', '', 'Moroccan Dirham'),
				(90, 'MDL', '', 'Moldovan Leu'),
				(91, 'MGA', '', 'Malagasy Ariary'),
				(92, 'MKD', 'ден', 'Macedonian Denar'),
				(93, 'MMK', '', 'Burmese Kyat'),
				(94, 'MNT', '₮', 'Mongolian Tughrik'),
				(95, 'MOP', '', 'Macau Pataca'),
				(96, 'MRO', '', 'Mauritanian Ouguiya'),
				(97, 'MUR', '₨', 'Mauritian Rupee'),
				(98, 'MVR', '', 'Maldivian Rufiyaa'),
				(99, 'MWK', '', 'Malawian Kwacha'),
				(100, 'MXN', '$', 'Mexican Peso'),
				(101, 'MYR', 'RM', 'Malaysian Ringgit'),
				(102, 'MZN', 'MT', 'Mozambican Metical'),
				(103, 'NAD', '$', 'Namibian Dollar'),
				(104, 'NGN', '₦', 'Nigerian Naira'),
				(105, 'NIO', 'C$', 'Nicaraguan Cordoba'),
				(106, 'NOK', 'kr', 'Norwegian Krone'),
				(107, 'NPR', '₨', 'Nepalese Rupee'),
				(108, 'NZD', '$', 'New Zealand Dollar'),
				(109, 'OMR', '﷼', 'Omani Rial'),
				(110, 'PAB', 'B/.', 'Panamanian Balboa'),
				(111, 'PEN', 'S/.', 'Peruvian Nuevo Sol'),
				(112, 'PGK', '', 'Papua New Guinean Kina'),
				(113, 'PHP', '₱', 'Philippine Peso'),
				(114, 'PKR', '₨', 'Pakistani Rupee'),
				(115, 'PLN', 'zł', 'Polish Zloty'),
				(116, 'PYG', 'Gs', 'Paraguayan Guarani'),
				(117, 'QAR', '﷼', 'Qatari Riyal'),
				(118, 'RON', 'lei', 'Romanian New Leu'),
				(119, 'RSD', 'Дин.', 'Serbian Dinar'),
				(120, 'RUB', '₽', 'Russian Ruble'),
				(121, 'RWF', '', 'Rwandan Franc'),
				(122, 'SAR', '﷼', 'Saudi or Saudi Arabian Riyal'),
				(123, 'SBD', '$', 'Solomon Islander Dollar'),
				(124, 'SCR', '₨', 'Seychellois Rupee'),
				(125, 'SDG', '', 'Sudanese Pound'),
				(126, 'SEK', 'kr', 'Swedish Krona'),
				(127, 'SGD', '$', 'Singapore Dollar'),
				(128, 'SHP', '£', 'Saint Helenian Pound'),
				(129, 'SLL', '', 'Sierra Leonean Leone'),
				(130, 'SOS', 'S', 'Somali Shilling'),
				(131, 'SPL', '', 'Seborgan Luigino'),
				(132, 'SRD', '$', 'Surinamese Dollar'),
				(133, 'STD', '', 'Sao Tomean Dobra'),
				(134, 'SVC', '$', 'Salvadoran Colon'),
				(135, 'SYP', '£', 'Syrian Pound'),
				(136, 'SZL', '', 'Swazi Lilangeni'),
				(137, 'THB', '฿', 'Thai Baht'),
				(138, 'TJS', '', 'Tajikistani Somoni'),
				(139, 'TMT', '', 'Turkmenistani Manat'),
				(140, 'TND', '', 'Tunisian Dinar'),
				(141, 'TOP', '', 'Tongan Pa''anga'),
				(142, 'TRY', '', 'Turkish Lira'),
				(143, 'TTD', 'TT$', 'Trinidadian Dollar'),
				(144, 'TVD', '$', 'Tuvaluan Dollar'),
				(145, 'TWD', 'NT$', 'Taiwan New Dollar'),
				(146, 'TZS', '', 'Tanzanian Shilling'),
				(147, 'UAH', '₴', 'Ukrainian Hryvna'),
				(148, 'UGX', '', 'Ugandan Shilling'),
				(149, 'USD', '$', 'US Dollar'),
				(150, 'UYU', '$', 'Uruguayan Peso'),
				(151, 'UZS', 'лв', 'Uzbekistani Som'),
				(152, 'VEF', 'Bs', 'Venezuelan Bolivar Fuerte'),
				(153, 'VND', '₫', 'Vietnamese Dong'),
				(154, 'VUV', '', 'NiVanuatu Vatu'),
				(155, 'WST', '', 'Samoan Tala'),
				(156, 'XAF', '', 'Central African CFA Franc BEAC'),
				(157, 'XAG', '', 'Silver Ounce'),
				(158, 'XAU', '', 'Gold Ounce'),
				(159, 'XCD', '$', 'East Caribbean Dollar'),
				(160, 'XDR', '', 'IMF Special Drawing Rights'),
				(161, 'XOF', '', 'CFA Franc'),
				(162, 'XPD', '', 'Palladium Ounce'),
				(163, 'XPF', '', 'CFP Franc'),
				(164, 'XPT', '', 'Platinum Ounce'),
				(165, 'YER', '﷼', 'Yemeni Rial'),
				(166, 'ZAR', 'R', 'South African Rand'),
				(167, 'ZMK', '', 'Zambian Kwacha'),
				(168, 'ZWD', 'Z$', 'Zimbabwean Dollar')";

        $results = $wpdb->get_results("select * from {$wpdb->prefix}bmp_currency");
        $rows = $wpdb->num_rows;
        if (empty($rows) || $rows < 168) {
            $wpdb->query($insert);
        }
    }

    public static function get_pages_array()
    {
        return apply_filters(
            'ump_create_pages',
            array(
                'register'      => array(
                    'name'    => _x('Register', 'Page slug', 'ump'),
                    'title'   => _x('Register', 'Page title', 'ump'),
                    'content' => '',
                ),
                'downlines'      => array(
                    'name'    => _x('Downlines', 'Page slug', 'ump'),
                    'title'   => _x('Downlines', 'Page title', 'ump'),
                    'content' => '',
                ),
            )
        );
    }


    public static function create_pages()
    {
        $pages = self::get_pages_array();
        // $menu = wp_get_nav_menu_object('primary');
        // if (empty($menu)) {
        //     wp_update_nav_menu_object(0, array('menu-name' => 'primary'));
        // }
        // $menu = wp_get_nav_menu_object('primary');

        // $args = array(
        //     "post_type" => "nav_menu_item",
        //     "name" => __('Unilevel MLM Plan'),
        //     'title' => __('Unilevel MLM Plan')
        // );

        // $query = new WP_Query($args);
        // if (empty($query->posts)) {
        //     $parent_id = wp_update_nav_menu_item(
        //         $menu->term_id,
        //         0,
        //         array(
        //             'menu-item-title' =>  __('Unilevel MLM Plan'),
        //             'menu-item-classes' => 'ump',
        //             'menu-item-url' => 'javascript:void(0)',
        //             'menu-item-status' => 'publish',
        //             'menu-item-type' => 'custom',
        //         )
        //     );

        //     update_post_meta($parent_id, 'menu_item_ump', 'ump');
        // } else {
        //     $parent_id = $query->posts[0]->ID;
        // }

        foreach ($pages as $key => $page) {
            // $page_id = get_page_by_title($page['title'], OBJECT, 'page');

            // if (empty($page_id)) {
            $pageid = ump_create_page(esc_sql($page['name']), 'ump_' . $key . '_page_id', $page['title'], $page['content'], !empty($page['parent']) ? ump_get_page_id($page['parent']) : '');
            // if (!empty($menu)) {
            //     wp_update_nav_menu_item($menu->term_id, 0, array(
            //         'menu-item-object-id' => $pageid,
            //         'menu-item-object' => 'page',
            //         'menu-item-title' =>  $page['post_title'],
            //         'menu-item-classes' => 'ump',
            //         'menu-item-status' => 'publish',
            //         'menu-item-type' => 'post_type',
            //         // 'menu-item-parent-id' => $parent_id,
            //     ));
            // }
        }
    }

    public static function deactivate()
    {
        global $wpdb;

        $install = new UMP_Install;
        $tables = $install->get_tables();

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }

        $mlmPages = array('Register', 'Downlines');


        //delete post from wp_posts and wp_postmeta table
        foreach ($mlmPages as $value) {
            $post_id = $wpdb->get_var("SELECT id from {$wpdb->prefix}posts where post_title = '$value'");
            wp_delete_post($post_id, true);
        }

        $results = $wpdb->get_results("SELECT p.id from {$wpdb->prefix}posts as p join {$wpdb->prefix}postmeta as pm on p.id=pm.post_id where pm.meta_key='menu_item_ump' AND pm.meta_value = 'Unilevel MLM Plan'");
        foreach ($results as $result) {
            wp_delete_post($result->id, true);
        }
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%ump_%';");
        $wpdb->query("DELETE FROM $wpdb->posts WHERE post_title ='Unilevel MLM Plan'");
        $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key=menu_item_ump");

        $wp_roles = new WP_Roles();
        $wp_roles->remove_role("ump_user");
    }
}
UMP_Install::init();
