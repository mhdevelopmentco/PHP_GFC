<?php
require('lib/init.php');
global $userClass, $mainClass, $paymentClass, $session_uid;

$email = '';
$first_name = '';
$last_name = '';
$invite_info = null;

$invite_code = isset($_GET['invite_code']) ? $_GET['invite_code'] : '';
$invited = $invite_code != '' ? true : false;

if ($invited) {
    $invite_info = $userClass->getInviteInfo($invite_code);
    if ($invite_info) {
        $email = $invite_info->email;
        $first_name = $invite_info->first_name;
        $last_name = $invite_info->last_name;
    }

    $session_uid = -1;

    session_start();
    session_unset();
//    session_destroy();
}

if (isLoggedIn()) {
    $url = BASE_URL . 'index.php';
    header("Location: $url"); // Page redirecting to home.php
    exit();
}

include('templates/default/header.php');
?>
    <div class="container-fluid content form-only-content">
        <div class="main-container">

            <?php
            if ($invited) {
                if ($invite_info) {
                    $ownerDetails = $userClass->userDetails($invite_info->user_id);
                    echo '<div class="search-examples"><center><p><b>Join ' . $ownerDetails->first_name . ' ' . $ownerDetails->last_name . '\'s team</b></p></center></div>';
                } else {
                    echo '<div class="search-examples"><center><p><b>Invalid invite link</b></p></center></div>';
                }
            }
            ?>

            <div class="row signup-form">
                <div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-2 col-lg-4 col-lg-offset-2">
                    <div class="h1 text-blue">Sign Up</div>
                    <p class="text-info">
                        GoFetchCode provides answers to your Building Code-related questions in a snap.
                        <br><br>As soon as you fill in the form on this page and subscribe, your free trial account
                        will start.
                        After 7 days, simply subscribe to one of our paid plans and keep using GoFetchCode for
                        your building code needs.
                        <br><br>Register now and get immediate and free access to the GoFetchCode search engine.
                    </p>
                </div>
                <div class="col-lg-4">

                    <div id="err_msg" class="text-error"></div>
                    <form name="form" method="get" id="register_form" autocomplete="off">

                        <input type="hidden" name="action" value="create_user">
                        <input type="hidden" name="invite_code" value="<?php echo $invite_code ?>">

                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <div class="form-group">
                                    <input type="text" name="first_name" value="<?php echo $first_name; ?>"
                                           pattern="[A-Za-z\s]{1,}" title="Must contain at least 1 or more characters"
                                           class="form-control" placeholder="First Name" required>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <div class="form-group">
                                    <input type="text" name="last_name" value="<?php echo $last_name; ?>"
                                           pattern="[A-Za-z\s]{1,}" title="Must contain at least 1 or more characters"
                                           class="form-control" placeholder="Last Name" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="text" name="username" value=""
                                   pattern="[A-Za-z0-9]{6,255}"
                                   title="Only uppercase, lowercase letters and digits are allowed."
                                   minlength="6" maxlength="255"
                                   class="form-control" placeholder="Username" required>
                        </div>

                        <div class="form-group">
                            <input type="password" name="password" value="" id="password"
                                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                   title="Must contain at least one number and one uppercase and lowercase letter, and at least
                            8 or more characters"
                                   class="form-control" placeholder="Password" required>
                        </div>


                        <div class="form-group">
                            <input type="password" name="password_2" value="" id="password2"
                                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                   title="Must contain at least one number and one uppercase and lowercase letter, and at least
                            8 or more characters"
                                   class="form-control" placeholder="Confirm Password" required>
                        </div>

                        <div class="form-group">
                            <input type="url" name="site_url" id="site_url" value=""

                                   pattern="https?://.+" title="Include http://"
                                   class="form-control" placeholder="Business URL" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" id="email" value="<?php echo $email; ?>"
                                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$"
                                   autocomplete="false"
                                   class="form-control" placeholder="Business Email" required>
                        </div>

                        <div class="form-group">
                            <input type="text" name="phone" value="" class="form-control"
                                   pattern="^\+?[0-9]\d{1,14}$"
                                   minlength="7" maxlength="15"
                                   title="Please input your valid phone number as E164 Format."
                                   placeholder="Phone" required>
                            <!--
                            //Pattern
                            //Origin: \(?\d{3}\)?\s?[\-]?\d{3}[\-]?\d{4} -- (ddd) ddd-dddd, (ddd) ddd dddd, ddd-ddd-dddd
                            //Common Format: ^\+(?:[0-9]\x20?){6,14}[0-9]$
                            //EPP FORMAT: ^\+[0-9]{1,3}\.[0-9]{4,14}(?:x.+)?$
                            -->
                        </div>

                        <div class="form-group">
                            <input type="text" name="organization" value=""
                                   class="form-control" placeholder="Organization" required>
                        </div>

                        <?php if (false && !$invited) { ?>
                            <div class="form-group<?php if ($errorMsgExtraUsers != '') echo ' has-error'; ?>">
                                <label>Amount of sub-accounts(for teams)</label>
                                <select name="extra_users" class="form-control">
                                    <?php
                                    foreach ($extra_users_tiers as $tier) {
                                        $selected = $extra_users == $tier ? ' selected' : '';
                                        echo '<option value="' . $tier . '"' . $selected . '>' . $tier . '</option>';
                                    }
                                    ?>
                                </select>

                            </div>

                            <div class="form-group<?php if ($errorMsgLocation != '') echo ' has-error'; ?>">
                                <label>Locations</label>
                                <div class="form-control">
                                    <?php
                                    foreach ($mainClass->getStates() as $state) {
                                        if ($state['id'] != 5 && $state['id'] != 10)
                                            continue;

                                        echo '<label class="col-sm-6 col-md-6"><input type="checkbox" name="locations[]" value="' . $state['id'] . '">' . $state['name'] . '</label>';
                                    }
                                    ?>
                                </div>
                            </div>

                        <?php } ?>

                        <div class="form-group">
                            <label><input type="checkbox" id="agree">I agree to the
                                <a href="termsofservice.php" target="_blank">GofetchCode Terms
                                    and Conditions</a></label>
                        </div>

                        <div class="form-actions form-group ">
                            <button type="submit" class="pri_button full-width" disabled id="signup_submit">
                                Sign up for a free trial
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

        //read temporary email list
        var temp_domains = [];

        $.getJSON("templates/default/file/temporary_emails.json", function (data) {
            $.each(data, function (key, val) {
                temp_domains.push(val);
            });
        });

        $('#agree').change(function () {

            var checked = $(this).prop('checked');

            if (checked) {
                $('#signup_submit').prop('disabled', false);
            } else {
                $('#signup_submit').prop('disabled', true);
            }
        });

        function show_error_msg(message) {

            var err_msg = $('#err_msg');
            $(err_msg).html(message);
            $(err_msg).fadeIn(2000).fadeOut(4000);
        }

        function submit_register_form() {

            var data = $('#register_form').serialize();

            $.ajax({
                url: 'manage_users.php',
                method: "GET",
                data: data,
                success: function (response) {

                    console.log(response);
                    if (response == "success") {
                        window.location = "end_signup.php";
                    } else {
                        show_error_msg(response);
                    }
                },
                error: function () {
                    console.log(response);
                    var err_msg = "Problem Occurred. Please try again later.";
                    show_error_msg(err_msg);
                }
            });
        }

        $('#register_form').submit(function (e) {

            e.preventDefault();

            //check password
            var password = $('#password').val();
            var password2 = $('#password2').val();
            if (password != password2) {

                show_error_msg('Password does not match.');
                return false;
            }

            //check email and url according to businees
            var url = $('#site_url').val();
            var email_obj = $('#email');
            var email = $(email_obj).val();
            var email_subfix = email.split('@');
            email_subfix = email_subfix[1];
            var post = $.inArray(email_subfix, temp_domains);
            //check personal email
            if (post >= 0) {
                show_error_msg('Please provide a valid business email.');
                $(email_obj).focus();
                return false;
            }

            var url_string = String(url);

            var contains = url_string.indexOf(email_subfix);

            if (contains === -1) {
                $.confirm({
                    title: 'Valid Business Email',
                    backgroundDismiss: true, // this will just close the modal
                    content: 'Is this your business email?',
                    buttons: {
                        Continue: {
                            text: "Yes",
                            btnClass: "btn-primary pri-button",
                            action: function () {
                                submit_register_form();
                            }
                        },
                        Cancel: {
                            text: "No",
                            btnClass: "btn-dark",
                            action: function () {
                                valid = false;
                                show_error_msg('You should input your valid Business Email.');
                                $(email_obj).focus();
                            }
                        }
                    }
                });
            } else {
                submit_register_form();
            }
        });
    </script>


<?php include('templates/default/footer.php'); ?>