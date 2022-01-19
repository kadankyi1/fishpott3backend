<?php
$active_page = "Business";
$page_name = "Add Business";
$page_title = "Add a new business to be used as a suggestion";
?>

@extends('admin.layouts.app')

<!-- SETTING THE CONTENT AS REQUIRED BY THE CORE STRUCTURE OF THE PAGE -->
@section('content')
<div class="pcoded-inner-content">
    <!-- Main-body start -->
    <div class="main-body">
        <div class="page-wrapper">
            <!-- Page-body start -->
            <div class="page-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Note </h5>
                                <span>Make sure the business information is <code>accurate, appealing and follows </code> to FishPott Business' <code>mission creating mutually beneficial relationships between </code> business and investor </span>
                            </div>
                            <div class="loader-holder offset-md-5" id="loader" style="display: none"><br><br><br><br><br><br><div class="myloader"></div></div>

                            <div class="card-block">
                                <form id="form">
                                    <h4 class="sub-title">Form</h4>
                                    <div class="row">
                                        <!-- START OF FIRST COLUMN -->
                                        <input id="administrator_phone_number"  name="administrator_phone_number" required type="hidden" class="form-control">
                                        <input id="administrator_sys_id"  name="administrator_sys_id" required type="hidden" class="form-control">
                                        <input id="frontend_key"  name="frontend_key" required type="hidden" class="form-control">

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Pott Name</label>
                                                <div class="col-sm-9">
                                                    <input id="business_pottname"  name="business_pottname" minlength="2" maxlength="15" type="text" class="form-control" placeholder="Pott Name">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Registration Number</label>
                                                <div class="col-sm-9">
                                                    <input id="business_registration_number"  name="business_registration_number" required minlength="5" maxlength="100" type="text" class="form-control" placeholder="Registration Number">
                                                </div>
                                            </div>        
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Type</label>
                                                <div class="col-sm-9">
                                                    <input id="business_type" name="business_type" minlength="5" maxlength="100" required type="text" class="form-control" placeholder="First Answer">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Logo (2MB Max - JPG/PNG)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_logo_file" name="business_logo_file" type="file" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Legal Name</label>
                                                <div class="col-sm-9">
                                                    <input id="business_full_name"  name="business_full_name" minlength="4" maxlength="150" required type="text" class="form-control" placeholder="Legal Name">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Stock Market Code</label>
                                                <div class="col-sm-9">
                                                    <input id="business_stockmarket_shortname"  name="business_stockmarket_shortname" minlength="1" maxlength="10" type="text" class="form-control" placeholder="Stock Market Code">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Descriptive Bio</label>
                                                <div class="col-sm-9">
                                                    <input id="business_descriptive_bio"  name="business_descriptive_bio" minlength="1" maxlength="150" required type="text" class="form-control" placeholder="Descriptive Bio">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Address</label>
                                                <div class="col-sm-9">
                                                    <input id="business_address"  name="business_address" minlength="5" maxlength="150" required type="text" class="form-control" placeholder="Address">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Country</label>
                                                <div class="col-sm-9">
                                                    <select id="business_country" name="business_country" class="form-control">
                                                        <option value="1">AF - AFGHANISTAN - Afghanista </option>
                                                        <option value="2">AL - ALBANIA - Albania  </option>
                                                        <option value="3">DZ - ALGERIA - Algeria </option>
                                                        <option value="4">AS - AMERICAN SAMOA - American Samoa </option>
                                                        <option value="5">AD - ANDORRA - Andorra  </option> 
                                                        <option value="6">AO - ANGOLA - Angola   </option>
                                                        <option value="7">AI - ANGUILLA - Anguilla </option>
                                                        <option value="8">AQ - ANTARCTICA - Antarctica </option>
                                                        <option value="9">AG - ANTIGUA AND BARBUDA - Antigua and Barbuda </option>
                                                        <option value="10">AR - ARGENTINA - Argentina  </option>
                                                        <option value="11">AM - ARMENIA - Armenia </option>
                                                        <option value="12">AW - ARUBA - Aruba </option>
                                                        <option value="13">AU - AUSTRALIA - Australia  </option>
                                                        <option value="14">AT - AUSTRIA - Austria</option>
                                                        <option value="15">AZ - AZERBAIJAN - Azerbaijan </option>
                                                        <option value="16">BS - BAHAMAS - Bahamas </option>
                                                        <option value="17">BH - BAHRAIN - Bahrain </option>
                                                        <option value="18">BD - BANGLADESH - Bangladesh </option>
                                                        <option value="19">BB - BARBADOS - Barbados </option>
                                                        <option value="20">BY - BELARUS - Belarus </option>
                                                        <option value="21">BE - BELGIUM - Belgium</option>
                                                        <option value="22">BZ - BELIZE - Belize </option>
                                                        <option value="23">BJ - BENIN - Benin </option>
                                                        <option value="24">BM - BERMUDA - Bermuda </option>
                                                        <option value="25">BT - BHUTAN - Bhutan </option>
                                                        <option value="26">BO - BOLIVIA - Bolivia </option>
                                                        <option value="27">BA - BOSNIA AND HERZEGOVINA - Bosnia and Herzegovina </option>
                                                        <option value="28">BW - BOTSWANA - Botswana </option>
                                                        <option value="29">BV - BOUVET ISLAND - Bouvet Island</option>
                                                        <option value="30">BR - BRAZIL - Brazil</option>
                                                        <option value="31">IO - BRITISH INDIAN OCEAN TERRITORY - British Indian Ocean Territory</option>
                                                        <option value="32">BN - BRUNEI DARUSSALAM - Brunei Darussalam </option>
                                                        <option value="33">BG - BULGARIA - Bulgaria </option>
                                                        <option value="34">BF - BURKINA FASO - Burkina Faso </option>
                                                        <option value="35">BI - BURUNDI - Burundi </option>
                                                        <option value="36">KH - CAMBODIA - Cambodia </option>
                                                        <option value="37">CM - CAMEROON - Cameroon </option>
                                                        <option value="38">CA - CANADA - Canada</option>
                                                        <option value="39">CV - CAPE VERDE - Cape Verde </option>
                                                        <option value="40">KY - CAYMAN ISLANDS - Cayman Islands - </option>
                                                        <option value="41">CF - CENTRAL AFRICAN REPUBLIC - Central African Republic </option>
                                                        <option value="42">TD - CHAD - Chad </option>
                                                        <option value="43">CL - CHILE - Chile </option>
                                                        <option value="44">CN - CHINA - China </option>
                                                        <option value="45">CX - CHRISTMAS ISLAND - Christmas Island</option>
                                                        <option value="46">CC - COCOS (KEELING) ISLANDS - Cocos </option>
                                                        <option value="47">CO - COLOMBIA - Colombia </option>
                                                        <option value="48">KM - COMOROS - Comoros </option>
                                                        <option value="49">CG - CONGO - Congo </option>
                                                        <option value="50">CD - CONGO, THE DEMOCRATIC REPUBLIC OF THE - Congo, the Democratic Republic of the </option>
                                                        <option value="51">CK - COOK ISLANDS - Cook Islands </option>
                                                        <option value="52">CR - COSTA RICA - Costa Rica </option>
                                                        <option value="53">CI - COTE D'IVOIRE - Cote D'Ivoire </option>
                                                        <option value="54">HR - CROATIA - Croatia </option>
                                                        <option value="55">CU - CUBA - Cuba </option>
                                                        <option value="56">CY - CYPRUS - Cyprus </option>
                                                        <option value="57">CZ - CZECH REPUBLIC - Czech Republic </option>
                                                        <option value="58">DK - DENMARK - Denmark </option>
                                                        <option value="59">DJ - DJIBOUTI - Djibouti </option>
                                                        <option value="60">DM - DOMINICA - Dominica - </option>
                                                        <option value="61">DO - DOMINICAN REPUBLIC - Dominican Republic - </option>
                                                        <option value="62">EC - ECUADOR - Ecuador </option>
                                                        <option value="63">EG - EGYPT - Egypt </option>
                                                        <option value="64">SV - EL SALVADOR - El Salvador </option>
                                                        <option value="65">GQ - EQUATORIAL GUINEA - Equatorial Guinea </option>
                                                        <option value="66">ER - ERITREA - Eritrea </option>
                                                        <option value="67">EE - ESTONIA - Estonia </option>
                                                        <option value="68">ET - ETHIOPIA - Ethiopia </option>
                                                        <option value="69">FK - FALKLAND ISLANDS (MALVINAS) - Falkland Islands </option>
                                                        <option value="70">FO - FAROE ISLANDS - Faroe Islands </option>
                                                        <option value="71">FJ - FIJI - Fiji </option>
                                                        <option value="72">FI - FINLAND - Finland </option>
                                                        <option value="73">FR - FRANCE - France </option>
                                                        <option value="74">GF - FRENCH GUIANA - French Guiana </option>
                                                        <option value="75">PF - FRENCH POLYNESIA - French Polynesia </option>
                                                        <option value="76">TF - FRENCH SOUTHERN TERRITORIES - French Southern Territories</option>
                                                        <option value="77">GA - GABON - Gabon </option>
                                                        <option value="78">GM - GAMBIA - Gambia </option>
                                                        <option value="79">GE - GEORGIA - Georgia </option>
                                                        <option value="80">DE - GERMANY - Germany </option>
                                                        <option value="81">GH - GHANA - Ghana </option>
                                                        <option value="82">GI - GIBRALTAR - Gibraltar </option>
                                                        <option value="83">GR - GREECE - Greece </option>
                                                        <option value="84">GL - GREENLAND - Greenland </option>
                                                        <option value="85">GD - GRENADA - Grenada - </option>
                                                        <option value="86">GP - GUADELOUPE - Guadeloupe </option>
                                                        <option value="87">GU - GUAM - Guam - </option>
                                                        <option value="88">GT - GUATEMALA - Guatemala </option>
                                                        <option value="89">GN - GUINEA - Guinea </option>
                                                        <option value="90">GW - GUINEA-BISSAU - Guinea-Bissau </option>
                                                        <option value="91">GY - GUYANA - Guyana </option>
                                                        <option value="92">HT - HAITI - Haiti </option>
                                                        <option value="93">HM - HEARD ISLAND AND MCDONALD ISLANDS - Heard Island and Mcdonald Islands</option>
                                                        <option value="94">VA - HOLY SEE VATICAN CITY STATE)</option>
                                                        <option value="95">HN - HONDURAS - Honduras </option>
                                                        <option value="96">HK - HONG KONG - Hong Kong </option>
                                                        <option value="97">HU - HUNGARY - Hungary </option>
                                                        <option value="98">IS - ICELAND - Iceland </option>
                                                        <option value="99">IN - INDIA - India </option>
                                                        <option value="100">ID - INDONESIA - Indonesia </option>
                                                        <option value="101">IR - IRAN, ISLAMIC REPUBLIC OF - Iran, Islamic Republic of </option>
                                                        <option value="102">IQ - IRAQ - Iraq </option>
                                                        <option value="103">IE - IRELAND - Ireland </option>
                                                        <option value="104">IL - ISRAEL - Israel </option>
                                                        <option value="105">IT - ITALY - Italy </option>
                                                        <option value="106">JM - JAMAICA - Jamaica - </option>
                                                        <option value="107">JP - JAPAN - Japan </option>
                                                        <option value="108">JO - JORDAN - Jordan </option>
                                                        <option value="109">KZ - KAZAKHSTAN - Kazakhstan</option>
                                                        <option value="110">KE - KENYA - Kenya </option>
                                                        <option value="111">KI - KIRIBATI - Kiribati </option>
                                                        <option value="112">KP - KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF - Korea, Democratic People's Republic of </option>
                                                        <option value="113">KR - KOREA, REPUBLIC OF - Korea, Republic of </option>
                                                        <option value="114">KW - KUWAIT - Kuwait </option>
                                                        <option value="115">KG - KYRGYZSTAN - Kyrgyzstan </option>
                                                        <option value="116">LA - LAO PEOPLE'S DEMOCRATIC REPUBLIC - Lao People's Democratic Republic </option>
                                                        <option value="117">LV - LATVIA - Latvia </option>
                                                        <option value="118">LB - LEBANON - Lebanon </option>
                                                        <option value="119">LS - LESOTHO - Lesotho </option>
                                                        <option value="120">LR - LIBERIA - Liberia </option>
                                                        <option value="121">LY - LIBYAN ARAB JAMAHIRIYA - Libyan Arab Jamahiriya </option>
                                                        <option value="122">LI - LIECHTENSTEIN - Liechtenstein </option>
                                                        <option value="123">LT - LITHUANIA - Lithuania </option>
                                                        <option value="124">LU - LUXEMBOURG - Luxembourg </option>
                                                        <option value="125">MO - MACAO - Macao </option>
                                                        <option value="126">MK - MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF - Macedonia, the Former Yugoslav Republic of </option>
                                                        <option value="127">MG - MADAGASCAR - Madagascar </option>
                                                        <option value="128">MW - MALAWI - Malawi </option>
                                                        <option value="129">MY - MALAYSIA - Malaysia </option>
                                                        <option value="130">MV - MALDIVES - Maldives </option>
                                                        <option value="131">ML - MALI - Mali </option>
                                                        <option value="132">MT - MALTA - Malta </option>
                                                        <option value="133">MH - MARSHALL ISLANDS - Marshall Islands </option>
                                                        <option value="134">MQ - MARTINIQUE - Martinique </option>
                                                        <option value="135">MR - MAURITANIA - Mauritania </option>
                                                        <option value="136">MU - MAURITIUS - Mauritius </option>
                                                        <option value="137">YT - MAYOTTE - Mayotte </option>
                                                        <option value="138">MX - MEXICO - Mexico </option>
                                                        <option value="139">FM - MICRONESIA, FEDERATED STATES OF - Micronesia, Federated States of </option>
                                                        <option value="140">MD - MOLDOVA, REPUBLIC OF - Moldova, Republic of </option>
                                                        <option value="141">MC - MONACO - Monaco </option>
                                                        <option value="142">MN - MONGOLIA - Mongolia </option>
                                                        <option value="143">MS - MONTSERRAT - Montserrat - </option>
                                                        <option value="144">MA - MOROCCO - Morocco </option>
                                                        <option value="145">MZ - MOZAMBIQUE - Mozambique </option>
                                                        <option value="146">MM - MYANMAR - Myanmar </option>
                                                        <option value="147">NA - NAMIBIA - Namibia </option>
                                                        <option value="148">NR - NAURU - Nauru </option>
                                                        <option value="149">NP - NEPAL - Nepal </option>
                                                        <option value="150">NL - NETHERLANDS - Netherlands </option>
                                                        <option value="151">AN - NETHERLANDS ANTILLES - Netherlands Antilles </option>
                                                        <option value="152">NC - NEW CALEDONIA - New Caledonia </option>
                                                        <option value="153">NZ - NEW ZEALAND - New Zealand </option>
                                                        <option value="154">NI - NICARAGUA - Nicaragua </option>
                                                        <option value="155">NE - NIGER - Niger </option>
                                                        <option value="156">NG - NIGERIA - Nigeria </option>
                                                        <option value="157">NU - NIUE - Niue </option>
                                                        <option value="158">NF - NORFOLK ISLAND - Norfolk Island </option>
                                                        <option value="159">MP - NORTHERN MARIANA ISLANDS - Northern Mariana Islands - </option>
                                                        <option value="160">NO - NORWAY - Norway </option>
                                                        <option value="161">OM - OMAN - Oman </option>
                                                        <option value="162">PK - PAKISTAN - Pakistan </option>
                                                        <option value="163">PW - PALAU - Palau </option>
                                                        <option value="164">PS - PALESTINIAN TERRITORY, OCCUPIED - Palestinian Territory, Occupied </option>
                                                        <option value="165">PA - PANAMA - Panama </option>
                                                        <option value="166">PG - PAPUA NEW GUINEA - Papua New Guinea </option>
                                                        <option value="167">PY - PARAGUAY - Paraguay </option>
                                                        <option value="168">PE - PERU - Peru </option>
                                                        <option value="169">PH - PHILIPPINES - Philippines </option>
                                                        <option value="170">PN - PITCAIRN - Pitcairn</option>
                                                        <option value="171">PL - POLAND - Poland </option>
                                                        <option value="172">PT - PORTUGAL - Portugal </option>
                                                        <option value="173">PR - PUERTO RICO - Puerto Rico - </option>
                                                        <option value="174">QA - QATAR - Qatar </option>
                                                        <option value="175">RE - REUNION - Reunion </option>
                                                        <option value="176">RO - ROMANIA - Romania </option>
                                                        <option value="177">RU - RUSSIAN FEDERATION - Russian Federation </option>
                                                        <option value="178">RW - RWANDA - Rwanda </option>
                                                        <option value="179">SH - SAINT HELENA - Saint Helena </option>
                                                        <option value="180">KN - SAINT KITTS AND NEVIS - Saint Kitts and Nevis - </option>
                                                        <option value="181">LC - SAINT LUCIA - Saint Lucia - </option>
                                                        <option value="182">PM - SAINT PIERRE AND MIQUELON - Saint Pierre and Miquelon </option>
                                                        <option value="183">VC - SAINT VINCENT AND THE GRENADINES - Saint Vincent and the Grenadines - </option>
                                                        <option value="184">WS - SAMOA - Samoa </option>
                                                        <option value="185">SM - SAN MARINO - San Marino </option>
                                                        <option value="186">ST - SAO TOME AND PRINCIPE - Sao Tome and Principe </option>
                                                        <option value="187">SA - SAUDI ARABIA - Saudi Arabia </option>
                                                        <option value="188">SN - SENEGAL - Senegal </option>
                                                        <option value="189">CS - SERBIA AND MONTENEGRO - Serbia and Montenegro </option>
                                                        <option value="190">SC - SEYCHELLES - Seychelles </option>
                                                        <option value="191">SL - SIERRA LEONE - Sierra Leone </option>
                                                        <option value="192">SG - SINGAPORE - Singapore </option>
                                                        <option value="193">SK - SLOVAKIA - Slovakia </option>
                                                        <option value="194">SI - SLOVENIA - Slovenia </option>
                                                        <option value="195">SB - SOLOMON ISLANDS - Solomon Islands </option>
                                                        <option value="196">SO - SOMALIA - Somalia </option>
                                                        <option value="197">ZA - SOUTH AFRICA - South Africa </option>
                                                        <option value="198">GS - SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS - South Georgia and the South Sandwich Islands</option>
                                                        <option value="199">ES - SPAIN - Spain </option>
                                                        <option value="200">LK - SRI LANKA - Sri Lanka </option>
                                                        <option value="201">SD - SUDAN - Sudan </option>
                                                        <option value="202">SR - SURINAME - Suriname </option>
                                                        <option value="203">SJ - SVALBARD AND JAN MAYEN - Svalbard and Jan Mayen </option>
                                                        <option value="204">SZ - SWAZILAND - Swaziland </option>
                                                        <option value="205">SE - SWEDEN - Sweden </option>
                                                        <option value="206">CH - SWITZERLAND - Switzerland </option>
                                                        <option value="207">SY - SYRIAN ARAB REPUBLIC - Syrian Arab Republic </option>
                                                        <option value="208">TW - TAIWAN, PROVINCE OF CHINA - Taiwan, Province of China </option>
                                                        <option value="209">TJ - TAJIKISTAN - Tajikistan </option>
                                                        <option value="210">TZ - TANZANIA, UNITED REPUBLIC OF - Tanzania, United Republic of </option>
                                                        <option value="211">TH - THAILAND - Thailand </option>
                                                        <option value="212">TL - TIMOR-LESTE - Timor-Leste </option>
                                                        <option value="213">TG - TOGO - Togo </option>
                                                        <option value="214">TK - TOKELAU - Tokelau </option>
                                                        <option value="215">TO - TONGA - Tonga </option>
                                                        <option value="216">TT - TRINIDAD AND TOBAGO - Trinidad and Tobago - </option>
                                                        <option value="217">TN - TUNISIA - Tunisia </option>
                                                        <option value="218">TR - TURKEY - Turkey </option>
                                                        <option value="219">TM - TURKMENISTAN - Turkmenistan - </option>
                                                        <option value="220">TC - TURKS AND CAICOS ISLANDS - Turks and Caicos Islands - </option>
                                                        <option value="221">TV - TUVALU - Tuvalu </option>
                                                        <option value="222">UG - UGANDA - Uganda </option>
                                                        <option value="223">UA - UKRAINE - Ukraine </option>
                                                        <option value="224">AE - UNITED ARAB EMIRATES - United Arab Emirates </option>
                                                        <option value="225">GB - UNITED KINGDOM - United Kingdom </option>
                                                        <option value="226">US - UNITED STATES - United States</option>
                                                        <option value="227">UM - UNITED STATES MINOR OUTLYING ISLANDS - United States Minor Outlying Islands</option>
                                                        <option value="228">UY - URUGUAY - Uruguay </option>
                                                        <option value="229">UZ - UZBEKISTAN - Uzbekistan </option>
                                                        <option value="230">VU - VANUATU - Vanuatu </option>
                                                        <option value="231">VE - VENEZUELA - Venezuela </option>
                                                        <option value="232">VN - VIET NAM - Viet Nam </option>
                                                        <option value="233">VG - VIRGIN ISLANDS, BRITISH - Virgin Islands, British </option>
                                                        <option value="234">VI - VIRGIN ISLANDS, U.S. - Virgin Islands, U.s. - </option>
                                                        <option value="235">WF - WALLIS AND FUTUNA - Wallis and Futuna </option>
                                                        <option value="236">EH - WESTERN SAHARA - Western Sahara </option>
                                                        <option value="237">YE - YEMEN - Yemen </option>
                                                        <option value="238">ZM - ZAMBIA - Zambia </option>
                                                        <option value="239">ZW - ZIMBABWE - Zimbabwe </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Start Date</label>
                                                <div class="col-sm-9">
                                                    <input id="business_start_date"  name="business_start_date" required type="date" class="form-control" placeholder="Start Date">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Website</label>
                                                <div class="col-sm-9">
                                                    <input id="business_website"  name="business_website" minlength="0" maxlength="150" type="text" class="form-control" placeholder="Website">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Pitch Text</label>
                                                <div class="col-sm-9">
                                                    <input id="business_pitch_text"  name="business_pitch_text" minlength="1" maxlength="100" required type="text" class="form-control" placeholder="Pitch Text">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Pitch Video (25MB Max - MP4)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_pitch_video" name="business_pitch_video" type="file" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Last Year Revenue (USD - $)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_lastyr_revenue_usd"  name="business_lastyr_revenue_usd" min="0" required type="number" class="form-control" placeholder="Last Year Revenue (USD - $)">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Last Year Profit (USD - $)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_lastyr_profit_or_loss_usd"  name="business_lastyr_profit_or_loss_usd" min="0" required type="number" class="form-control" placeholder="Last Year Profit (USD - $)">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Debt (USD - $)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_debt_usd"  name="business_debt_usd" min="0" required type="number" class="form-control" placeholder="Debt (USD - $)">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Cash-On-Hand (USD - $)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_cash_on_hand_usd"  name="business_cash_on_hand_usd" min="0" required type="number" class="form-control" placeholder="Cash-On-Hand (USD - $)">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Net Worth (USD - $)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_net_worth_usd"  name="business_net_worth_usd" min="0" required type="number" class="form-control" placeholder="Net Worth (USD - $)">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">PIN</label>
                                                <div class="col-sm-9">
                                                    <input id="administrator_pin" name="administrator_pin" type="password" class="form-control" placeholder="PIN">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END OF FIRST COLUMN -->
                                        <!-- START OF SECOND COLUMN -->
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Price Per Stock (USD - $)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_price_per_stock_usd"  name="business_price_per_stock_usd" min="0" required type="number" class="form-control" placeholder="Price Per Stock (USD - $)">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Investment Amt Needed (USD - $)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_investments_amount_needed_usd"  name="business_investments_amount_needed_usd" min="1" required type="number" class="form-control" placeholder="Investment Amount Needed">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Max. Investors</label>
                                                <div class="col-sm-9">
                                                    <input id="business_maximum_number_of_investors_allowed"  name="business_maximum_number_of_investors_allowed" min="1" required type="number" class="form-control" placeholder="Max. Investors">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Current Shareholders</label>
                                                <div class="col-sm-9">
                                                    <input id="business_current_shareholders"  name="business_current_shareholders" min="1" required type="number" class="form-control" placeholder="Current Shareholders">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Full Financial Report (10MB Max - PDF)</label>
                                                <div class="col-sm-9">
                                                    <input id="business_full_financial_report_pdf_url" name="business_full_financial_report_pdf_url" type="file" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Descriptive Financial Bio</label>
                                                <div class="col-sm-9">
                                                    <input id="business_descriptive_financial_bio"  name="business_descriptive_financial_bio" minlength="0" maxlength="150" required type="text" class="form-control" placeholder="Descriptive Financial Bio">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">CEO First Name</label>
                                                <div class="col-sm-9">
                                                    <input id="business_executive1_position"  name="business_executive1_position" value="CEO" required type="hidden" class="form-control" placeholder="ceo_position">
                                                    <input id="business_executive1_firstname"  name="business_executive1_firstname" minlength="1" maxlength="100" required type="text" class="form-control" placeholder="CEO First Name">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">CEO Last Name</label>
                                                <div class="col-sm-9">
                                                    <input id="business_executive1_lastname"  name="business_executive1_lastname" minlength="1" maxlength="100" required type="text" class="form-control" placeholder="CEO Last Name">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">CEO Phone</label>
                                                <div class="col-sm-9">
                                                    <input id="business_executive1_phone"  name="business_executive1_phone" minlength="1" maxlength="100" required type="text" class="form-control" placeholder="CEO Phone">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">CEO Email</label>
                                                <div class="col-sm-9">
                                                    <input id="business_executive1_email"  name="business_executive1_email" minlength="1" maxlength="100" required type="email" class="form-control" placeholder="CEO Email">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">CFO First Name</label>
                                                <div class="col-sm-9">
                                                    <input id="business_executive2_position"  name="business_executive2_position" value="CFO" required type="hidden" class="form-control" placeholder="ceo_position">
                                                    <input id="business_executive2_firstname"  name="business_executive2_firstname" minlength="1" maxlength="100" required type="text" class="form-control" placeholder="CFO First Name">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">CFO Last Name</label>
                                                <div class="col-sm-9">
                                                    <input id="business_executive2_lastname"  name="business_executive2_lastname" minlength="1" maxlength="100" required type="text" class="form-control" placeholder="CFO Last Name">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">CFO Phone</label>
                                                <div class="col-sm-9">
                                                    <input id="business_executive2_phone"  name="business_executive2_phone" minlength="1" maxlength="15" required type="text" class="form-control" placeholder="CFO Phone">
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">CFO Email</label>
                                                <div class="col-sm-9">
                                                    <input id="business_executive2_email"  name="business_executive2_email" minlength="1" maxlength="100" required type="email" class="form-control" placeholder="CFO Email">
                                                </div>
                                            </div> 

                                        </div>
                                        <!-- END OF SECOND COLUMN -->
                                    </div>
                                    <!-- END OF ROW FOR COLUMN SPLITTING -->
                                    <div class="row m-t-30">
                                        <div class="offset-md-4 col-md-4">
                                            <input type="submit" value="Save Business" class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20"/>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Main-body end -->
                            <div id="styleSelector">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page-body end -->
        </div>
    </div>
</div>
@endsection


    @section('bottom-scripts');    
        <!-- Required Jquery -->
        <script type="text/javascript" src="/js/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="/js/jquery-ui/jquery-ui.min.js "></script>
        <script type="text/javascript" src="/js/popper.js/popper.min.js"></script>
        <script type="text/javascript" src="/js/bootstrap/js/bootstrap.min.js "></script>
        <script type="text/javascript" src="/pages/widget/excanvas.js "></script>
        <!-- waves js -->
        <script src="/pages/waves/js/waves.min.js"></script>
        <!-- jquery slimscroll js -->
        <script type="text/javascript" src="/js/jquery-slimscroll/jquery.slimscroll.js "></script>
        <!-- modernizr js -->
        <script type="text/javascript" src="/js/modernizr/modernizr.js "></script>
        <!-- slimscroll js -->
        <script type="text/javascript" src="/js/SmoothScroll.js"></script>
        <script src="/js/jquery.mCustomScrollbar.concat.min.js "></script>
        <!-- Chart js -->
        <script type="text/javascript" src="/js/chart.js/Chart.js"></script>
        <!-- amchart js -->
        <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
        <script src="/pages/widget/amchart/gauge.js"></script>
        <script src="/pages/widget/amchart/serial.js"></script>
        <script src="/pages/widget/amchart/light.js"></script>
        <script src="/pages/widget/amchart/pie.min.js"></script>
        <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
        <!-- menu js -->
        <script src="/js/pcoded.min.js"></script>
        <script src="/js/vertical-layout.min.js "></script>
        <!-- custom js -->
        <script type="text/javascript" src="/pages/dashboard/custom-dashboard.js"></script>
        <script type="text/javascript" src="/js/script.js "></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-noty/2.3.7/packaged/jquery.noty.packaged.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css">
        <script type="text/javascript" src="/js/custom/config.js "></script>
        <script type="text/javascript" src="/js/custom/business/add-business.js "></script>
    @endsection
