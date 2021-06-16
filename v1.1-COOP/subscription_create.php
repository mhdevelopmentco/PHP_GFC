<?php
require('lib/init.php');
requireNotSubAccount();

if (is_exist_Subscribed()) {
    $url = BASE_URL . 'search.php';
    header("Location: $url");
    exit();
}

global $mainClass;

$states = $mainClass->getStates();
include('templates/default/header.php');

$cur_month = date('n');
$cur_year = date('Y');
$last_year = $cur_year + 10;
?>

    <div class="container-fluid content">
        <div class="main-container">
            <div class="row">

                <div class="row">

                    <div class="col-md-6">

                        <div class="sub_col">

                            <form id="create_subscription_form" action="subscription_manage.php"
                                  method="post" class="sub_form">

                                <input type="hidden" name="total_price" id="total_price">
                                <input type="hidden" name="create_subscription" value="1">

                                <div class="control">
                                    Please choose all the state building codes you would like to subscribe to
                                </div>

                                <div class="row" style="padding: 10px;">

                                    <!--div class="col-xs-4 control">
                                        <div class="squaredOne">
                                            <input class="location_check location"
                                                   data-location="<?php echo $state['name'] ?>"
                                                   name="locations[]"
                                                   type="checkbox"
                                                   data-one="<?php echo $state['personal_price'] ?>"
                                                   data-team="<?php echo $state['team_price'] ?>"
                                                   id="location_<?php echo $state['id'] ?>"
                                                   value="<?php echo $state['id'] ?>"/>
                                            <label
                                                for="location_<?php echo $state['id'] ?>"><?php echo $state['name'] ?></label>
                                        </div>
                                    </div-->
                                    <label class="hidden" for="location_select">Location Select</label>
                                    <select id="location_select" multiple="multiple" name="locations">
                                        <?php
                                        foreach ($states as $state) {
                                            ?>
                                            <option
                                                data-location="<?php echo $state['name'] ?>"
                                                data-one="<?php echo $state['personal_price'] ?>"
                                                data-team="<?php echo $state['team_price'] ?>"
                                                value="<?php echo $state['id']; ?>"><?php echo $state['name']; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="hidden">
                                    <label for="selected_locations" class="hidden">
                                        <input type="hidden" id="selected_locations" value="" class="hidden"
                                               name="selected_locations">
                                    </label>
                                </div>

                                <div class="form-group">
                                    <div class="control">
                                        What is your team size?
                                    </div>
                                    <p class="description">Enter number between 1 to 100. You will be
                                        charged per seat.</p>

                                    <input type="number" name="extra_users" class="form-control"
                                           placeholder="1" value="1" min="1" max="100" required>
                                </div>


                                <div class="h1 text-blue">Payment information</div>

                                <div class="control">
                                    <img src="templates/default/images/visa_master_logo.png">
                                </div>

                                <div class="form-group">
                                    <input type="text" name="cc_number" class="form-control"
                                           placeholder="Debit or credit card number"
                                           value="" id="cc_number"
                                           autocomplete="on" pattern="[0-9]{13,16}"
                                           required>
                                </div>

                                <div class="row">
                                    <div class="col-xs-12 col-sm-8">
                                        <label for="cc_expire_month">Expiration Date</label>

                                        <div class="row">
                                            <div class="form-group col-sm-6 col-xs-12">
                                                <select name="cc_expire_month" class="form-control"
                                                        id="cc_expire_month"
                                                        autocomplete="on" required>
                                                    <option value="0">MM</option>
                                                    <?php
                                                    for ($month = 1; $month <= 12; $month++) {
                                                        if ($month == $cur_month) {
                                                            echo '<option selected value="' . $month . '">' . $month . '</option>';
                                                        } else {
                                                            echo '<option value="' . $month . '">' . $month . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group  col-sm-6 col-xs-12">

                                                <select name="cc_expire_year" class="form-control"
                                                        id="cc_expire_year" autocomplete="on"
                                                        required>
                                                    <option value="0">YYYY</option>
                                                    <?php
                                                    for ($year = $cur_year; $year < $last_year; $year++) {
                                                        if ($year == $cur_year) {
                                                            echo '<option selected value="' . $year . '">' . $year . '</option>';
                                                        } else {
                                                            echo '<option value="' . $year . '">' . $year . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-4">
                                        <label for="cc_csc">Security code</label>
                                        <div class="form-group">
                                            <input type="text" name="cc_csc" class="form-control"
                                                   id="cc_csc" placeholder="CVC" pattern="[0-9]{3,4}"
                                                   title="3 or 4 digits"
                                                   autocomplete="on" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <input type="text" name="cc_name" class="form-control" id="cc_name"
                                           placeholder="Name on card" autocomplete="on"
                                           required>
                                </div>

                                <div class="form-group">
                                    <input type="tel" name="customer_phone" class="form-control"
                                           id="customer_phone"
                                           pattern="\(?\d{3}\)?\s?[\-]?\d{3}[\-]?\d{4}"
                                           title="(ddd) ddd-dddd or ddd-ddd-dddd"
                                           placeholder="Phone number" autocomplete="on"
                                           required>
                                </div>

                                <div class="form-group">
                                    <input type="text" name="cc_address" class="form-control"
                                           id="cc_address"
                                           placeholder="Address" autocomplete="on"
                                           required>
                                </div>

                                <div class="form-group">
                                    <input type="text" name="cc_city" class="form-control"
                                           placeholder="City" id="cc_city"
                                           autocomplete="on" required>
                                </div>

                                <div class="form-group">
                                    <input type="text" name="cc_state" class="form-control"
                                           placeholder="State" id="cc_state"
                                           autocomplete="on" required>
                                </div>

                                <div class="form-group">
                                    <input type="text" name="cc_zip" class="form-control"
                                           placeholder="Zip or postal code"
                                           pattern="\d{5}([\-]\d{4})?"
                                           title="xxxxx or xxxxx-xxx"
                                           autocomplete="on" id="cc_zip"
                                           required>
                                </div>

                                <div class="form-group">
                                    <select name="cc_country" class="form-control"
                                            id="cc_country"
                                            autocomplete="billing country" required>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Albania">Albania</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="American Samoa">American Samoa</option>
                                        <option value="Andorra">Andorra</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Anguilla">Anguilla</option>
                                        <option value="Antartica">Antarctica</option>
                                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                        <option value="Argentina">Argentina</option>
                                        <option value="Armenia">Armenia</option>
                                        <option value="Aruba">Aruba</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Austria">Austria</option>
                                        <option value="Azerbaijan">Azerbaijan</option>
                                        <option value="Bahamas">Bahamas</option>
                                        <option value="Bahrain">Bahrain</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Barbados">Barbados</option>
                                        <option value="Belarus">Belarus</option>
                                        <option value="Belgium">Belgium</option>
                                        <option value="Belize">Belize</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Bermuda">Bermuda</option>
                                        <option value="Bhutan">Bhutan</option>
                                        <option value="Bolivia">Bolivia</option>
                                        <option value="Bosnia and Herzegowina">Bosnia and Herzegowina
                                        </option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Bouvet Island">Bouvet Island</option>
                                        <option value="Brazil">Brazil</option>
                                        <option value="British Indian Ocean Territory">British Indian
                                            Ocean
                                            Territory
                                        </option>
                                        <option value="Brunei Darussalam">Brunei Darussalam</option>
                                        <option value="Bulgaria">Bulgaria</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cambodia">Cambodia</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Cape Verde">Cape Verde</option>
                                        <option value="Cayman Islands">Cayman Islands</option>
                                        <option value="Central African Republic">Central African
                                            Republic
                                        </option>
                                        <option value="Chad">Chad</option>
                                        <option value="Chile">Chile</option>
                                        <option value="China">China</option>
                                        <option value="Christmas Island">Christmas Island</option>
                                        <option value="Cocos Islands">Cocos (Keeling) Islands</option>
                                        <option value="Colombia">Colombia</option>
                                        <option value="Comoros">Comoros</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Congo">Congo, the Democratic Republic of the
                                        </option>
                                        <option value="Cook Islands">Cook Islands</option>
                                        <option value="Costa Rica">Costa Rica</option>
                                        <option value="Cota D'Ivoire">Cote d'Ivoire</option>
                                        <option value="Croatia">Croatia (Hrvatska)</option>
                                        <option value="Cuba">Cuba</option>
                                        <option value="Cyprus">Cyprus</option>
                                        <option value="Czech Republic">Czech Republic</option>
                                        <option value="Denmark">Denmark</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Dominica">Dominica</option>
                                        <option value="Dominican Republic">Dominican Republic</option>
                                        <option value="East Timor">East Timor</option>
                                        <option value="Ecuador">Ecuador</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="El Salvador">El Salvador</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Eritrea">Eritrea</option>
                                        <option value="Estonia">Estonia</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Falkland Islands">Falkland Islands (Malvinas)
                                        </option>
                                        <option value="Faroe Islands">Faroe Islands</option>
                                        <option value="Fiji">Fiji</option>
                                        <option value="Finland">Finland</option>
                                        <option value="France">France</option>
                                        <option value="France Metropolitan">France, Metropolitan
                                        </option>
                                        <option value="French Guiana">French Guiana</option>
                                        <option value="French Polynesia">French Polynesia</option>
                                        <option value="French Southern Territories">French Southern
                                            Territories
                                        </option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambia">Gambia</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Gibraltar">Gibraltar</option>
                                        <option value="Greece">Greece</option>
                                        <option value="Greenland">Greenland</option>
                                        <option value="Grenada">Grenada</option>
                                        <option value="Guadeloupe">Guadeloupe</option>
                                        <option value="Guam">Guam</option>
                                        <option value="Guatemala">Guatemala</option>
                                        <option value="Guinea">Guinea</option>
                                        <option value="Guinea-Bissau">Guinea-Bissau</option>
                                        <option value="Guyana">Guyana</option>
                                        <option value="Haiti">Haiti</option>
                                        <option value="Heard and McDonald Islands">Heard and Mc Donald
                                            Islands
                                        </option>
                                        <option value="Holy See">Holy See (Vatican City State)</option>
                                        <option value="Honduras">Honduras</option>
                                        <option value="Hong Kong">Hong Kong</option>
                                        <option value="Hungary">Hungary</option>
                                        <option value="Iceland">Iceland</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iran">Iran (Islamic Republic of)</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Ireland">Ireland</option>
                                        <option value="Israel">Israel</option>
                                        <option value="Italy">Italy</option>
                                        <option value="Jamaica">Jamaica</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Kiribati">Kiribati</option>
                                        <option value="Democratic People's Republic of Korea">Korea,
                                            Democratic
                                            People's Republic of
                                        </option>
                                        <option value="Korea">Korea, Republic of</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                                        <option value="Lao">Lao People's Democratic Republic</option>
                                        <option value="Latvia">Latvia</option>
                                        <option value="Lebanon" selected>Lebanon</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya
                                        </option>
                                        <option value="Liechtenstein">Liechtenstein</option>
                                        <option value="Lithuania">Lithuania</option>
                                        <option value="Luxembourg">Luxembourg</option>
                                        <option value="Macau">Macau</option>
                                        <option value="Macedonia">Macedonia, The Former Yugoslav
                                            Republic of
                                        </option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Malta">Malta</option>
                                        <option value="Marshall Islands">Marshall Islands</option>
                                        <option value="Martinique">Martinique</option>
                                        <option value="Mauritania">Mauritania</option>
                                        <option value="Mauritius">Mauritius</option>
                                        <option value="Mayotte">Mayotte</option>
                                        <option value="Mexico">Mexico</option>
                                        <option value="Micronesia">Micronesia, Federated States of
                                        </option>
                                        <option value="Moldova">Moldova, Republic of</option>
                                        <option value="Monaco">Monaco</option>
                                        <option value="Mongolia">Mongolia</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Namibia">Namibia</option>
                                        <option value="Nauru">Nauru</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="Netherlands">Netherlands</option>
                                        <option value="Netherlands Antilles">Netherlands Antilles
                                        </option>
                                        <option value="New Caledonia">New Caledonia</option>
                                        <option value="New Zealand">New Zealand</option>
                                        <option value="Nicaragua">Nicaragua</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Niue">Niue</option>
                                        <option value="Norfolk Island">Norfolk Island</option>
                                        <option value="Northern Mariana Islands">Northern Mariana
                                            Islands
                                        </option>
                                        <option value="Norway">Norway</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palau">Palau</option>
                                        <option value="Panama">Panama</option>
                                        <option value="Papua New Guinea">Papua New Guinea</option>
                                        <option value="Paraguay">Paraguay</option>
                                        <option value="Peru">Peru</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Pitcairn">Pitcairn</option>
                                        <option value="Poland">Poland</option>
                                        <option value="Portugal">Portugal</option>
                                        <option value="Puerto Rico">Puerto Rico</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Reunion">Reunion</option>
                                        <option value="Romania">Romania</option>
                                        <option value="Russia">Russian Federation</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis
                                        </option>
                                        <option value="Saint LUCIA">Saint LUCIA</option>
                                        <option value="Saint Vincent">Saint Vincent and the Grenadines
                                        </option>
                                        <option value="Samoa">Samoa</option>
                                        <option value="San Marino">San Marino</option>
                                        <option value="Sao Tome and Principe">Sao Tome and Principe
                                        </option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Senegal">Senegal</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="Sierra">Sierra Leone</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Slovakia">Slovakia (Slovak Republic)</option>
                                        <option value="Slovenia">Slovenia</option>
                                        <option value="Solomon Islands">Solomon Islands</option>
                                        <option value="Somalia">Somalia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="South Georgia">South Georgia and the South
                                            Sandwich
                                            Islands
                                        </option>
                                        <option value="Span">Spain</option>
                                        <option value="SriLanka">Sri Lanka</option>
                                        <option value="St. Helena">St. Helena</option>
                                        <option value="St. Pierre and Miguelon">St. Pierre and Miquelon
                                        </option>
                                        <option value="Sudan">Sudan</option>
                                        <option value="Suriname">Suriname</option>
                                        <option value="Svalbard">Svalbard and Jan Mayen Islands</option>
                                        <option value="Swaziland">Swaziland</option>
                                        <option value="Sweden">Sweden</option>
                                        <option value="Switzerland">Switzerland</option>
                                        <option value="Syria">Syrian Arab Republic</option>
                                        <option value="Taiwan">Taiwan, Province of China</option>
                                        <option value="Tajikistan">Tajikistan</option>
                                        <option value="Tanzania">Tanzania, United Republic of</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tokelau">Tokelau</option>
                                        <option value="Tonga">Tonga</option>
                                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                        <option value="Tunisia">Tunisia</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="Turkmenistan">Turkmenistan</option>
                                        <option value="Turks and Caicos">Turks and Caicos Islands
                                        </option>
                                        <option value="Tuvalu">Tuvalu</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="Ukraine">Ukraine</option>
                                        <option value="United Arab Emirates">United Arab Emirates
                                        </option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="United States" selected>United States</option>
                                        <option value="United States Minor Outlying Islands">United
                                            States
                                            Minor
                                            Outlying Islands
                                        </option>
                                        <option value="Uruguay">Uruguay</option>
                                        <option value="Uzbekistan">Uzbekistan</option>
                                        <option value="Vanuatu">Vanuatu</option>
                                        <option value="Venezuela">Venezuela</option>
                                        <option value="Vietnam">Viet Nam</option>
                                        <option value="Virgin Islands (British)">Virgin Islands
                                            (British)
                                        </option>
                                        <option value="Virgin Islands (U.S)">Virgin Islands (U.S.)
                                        </option>
                                        <option value="Wallis and Futana Islands">Wallis and Futuna
                                            Islands
                                        </option>
                                        <option value="Western Sahara">Western Sahara</option>
                                        <option value="Yemen">Yemen</option>
                                        <option value="Yugoslavia">Yugoslavia</option>
                                        <option value="Zambia">Zambia</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </select>
                                </div>

                                <div class="form-actions form-group ">
                                    <button type="button" id="create_subscription" class="pri_button full-width">
                                        Subscribe
                                    </button>
                                </div>

                            </form>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="sub_col summary sub_form" id="summary">

                            <div class="h1 text-blue">
                                Payment Summary
                            </div>

                            <p class="team_size">Team Size: <span id="team_size"></span></p>

                            <table class="table" id="order_summary">
                                <tbody>
                                </tbody>
                            </table>

                            <label style="margin-top: 20px;">Your subscription will be billed
                                monthly.</label>
                            <p class="description">***No charge will be processed on your card during the
                                free trial.
                                You may choose to cancel anytime***
                            </p>

                            <div class="benefit">
                                <label>Premium Benefits</label>
                                <ul>
                                    <li>Access to the most sophisticated Building Code search engine
                                        available.
                                    </li>
                                    <li>Unlimited search queries per user. Whatever the question you have,
                                        just ask
                                        GoFetchCode.
                                    </li>
                                    <li>Detailed answers to your questions. Our software brings you to
                                        exactly the right
                                        paragraph
                                        in the Code you subscribe to.
                                    </li>
                                    <li>
                                        Less time and energy spent on looking through endless pages of Code
                                        with our efficiency
                                        tools.
                                        Save your searches, bookmark specific articles, or share and rate
                                        the answer provided.
                                    </li>
                                </ul>
                            </div>


                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="templates/default/js/chargeover.js"></script>
    <script src="templates/default/js/jquery.validate.min.js"></script>
    <script>

        ChargeOver.Core.setup({
            'instance': 'gofetchcode.chargeover.com',
            'token': 'OXq2eAhT8V3bNr0QpGlWLUmI5JtnFc1z'
        });

        $(document).ready(function () {
            $('#location_select').multiselect({
                onChange: function (option, checked, select) {

                    var checked_location = $(option).data('location');
                    var is_florida = (checked_location == 'Florida');

                    var opt_val = $(option).val();

                    if (is_florida) {
                        updateSummary();

                    } else {
                        alert('Thank you for your interest in GofetchCode Building Code. Currently we do not have building codes for ' + checked_location + '. We will email you once these codes are available. Stay tuned!');
                        $('#location_select').multiselect('deselect', opt_val);

                    }
                }
            });

            $('[name=extra_users]').change(function () {
                validateTeamSize()
                updateSummary()
            });
        });

        //setInterval(updateSummary, 1000);

        function validateTeamSize() {
            if ($('[name=extra_users]').val() > 100)
                $('[name=extra_users]').val(100);
            else if ($('[name=extra_users]').val() < 1)
                $('[name=extra_users]').val(1);
        }

        function updateSummary() {

            var team_size = $('[name=extra_users]').val();

            var table = $('#order_summary').find('tbody');

            $(table).find('tr').remove();

            if (team_size < 1 || $('#location_select option:selected').length < 1) {
                $('#summary').hide();
                return;
            } else {
                $('#summary').show();
            }

            $('#team_size').text(team_size);

            table.append('<tr><td>Search Codes Location</td><td>Price</td><td align="right">Subtotal</td></tr>');

            var total = 0;

            var is_team = team_size > 1 ? true : false;


            $('#location_select option:selected').each(function () {

                var location_name = $(this).data('location');

                if (is_team) {
                    var location_price = $(this).attr('data-team');
                } else {
                    var location_price = $(this).attr('data-one');
                }

                total += (location_price * team_size);
                table.append('<tr><td>' + location_name + '</td><td>' + location_price + ' USD x ' + team_size + '</td><td align="right">' + (location_price * team_size) + ' USD</td></tr>');
            });


            table.append('<tr><td><b>Total: ' + total + ' USD</b></td><td></td><td></td></tr>');

            $('#total_price').val(total);
        }

        function my_callback_function(code, message, response) {

            if (code == 200) {
                $('#create_subscription_form').submit();
            } else {
                alert(message);
            }
        }

        $('#create_subscription').click(function (e) {

            //check location size
            var location_count = $('#location_select option:selected').length;
            if (location_count < 1) {
                alert('Please choose one location at least');
                return false;
            }

            //check valid credit card
            $('#selected_locations').val($('#location_select').val());

            //validate form elements
            if (!$('#create_subscription_form').valid())
                return false;

            //card number
            var card_number = $('#cc_number').val();
            var month = $('#cc_expire_month').val();
            var year = $('#cc_expire_year').val();
            var card_csc = $('#cc_csc').val();
            var card_holder = $('#cc_name').val();
            var phone = $('#customer_phone').val();
            var addr = $('#cc_address').val();
            var city = $('#cc_city').val();
            var state = $('#cc_state').val();
            var zip_code = $('#cc_zip').val();
            var country = $('#cc_country option:selected').val();


            var my_data = {
                number: card_number,
                expdate_month: month,
                expdate_year: year,
                name: card_holder,
                // CVV/CSC card security code (used only for card validation, not stored)
                cvv: card_csc,
                // Optional address information (can be used for address verification)
                address: addr,
                city: city,
                state: state,
                postcode: zip_code,
                country: country
            };

            //check validate credit card
            ChargeOver.CreditCard.validate(my_data, my_callback_function);
        })
    </script>

<?php include('templates/default/footer.php'); ?>