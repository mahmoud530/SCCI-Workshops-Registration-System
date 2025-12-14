<?php
require_once'check_registration.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCCI Workshops Registration</title>
    <link rel="icon" type="image/png" href="./assets/img/SCCI_Logo.png">
    <link rel="stylesheet" href="./assets/css/form.css">
    
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">
            <img src="./assets/img/SCCI_Logo.png" alt="Logo" width="74" height="74">
            </div>
            <p>SCCI Registration</p>
        </div>
        <!-- ستايل SUCCESS -->

        <div class="success" id="successMessage">
            Registration successful! We will contact you soon.
        </div>
        <!-- //======================= -->
            <!-- form start -->
        <!-- //====================== -->
        <form id="registrationForm">
            <!-- ستايل الليمت -->
            <div id="limitError" style="display:none; 
            background:#ffe5e5; 
            padding:12px; 
            border:1px solid #ff4d4d; 
            color:#b30000; 
            border-radius:6px; 
            margin-bottom:15px;
            font-weight:600;">
            </div>

            <div class="form-group">
                <label for="name">Full Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" required autocomplete="name">
                <div class="error" id="nameError"></div>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" required autocomplete="email">
                <div class="error" id="emailError"></div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" required autocomplete="tel">
                <div class="error" id="phoneError"></div>
            </div>
            <!-- from here we have to edit in js/process/database -->
            <div class="form-group">
                <label for="university">University <span class="required">*</span></label>
                <input type="text" id="university" name="university" required autocomplete="tel">
                <div class="error" id="universityError"></div>
            </div>

            <div class="form-group">
                <label for="faculty">Faculty <span class="required">*</span></label>
                <input type="text" id="faculty" name="faculty" required autocomplete="tel">
                <div class="error" id="facultyError"></div>
            </div>

            <div class="form-group">
                <label for="level">Level <span class="required">*</span></label>
                <input type="text" id="level" name="level" required autocomplete="tel">
                <div class="error" id="levelError"></div>
            </div>
            <div class="preferences-section">
                <h3>Choose Workshops</h3>
                <div class="info">
                    Select 3 different workshops based on your interest
                </div>

                <div class="preference-item">
                    <label for="first_preference">First Choice <span class="required">*</span></label>
                    <select id="first_preference" name="first_preference" required>
                        <option value="">Select your first workshop</option>
                        <option value="Devology">Devology - Full stack Web-Development Workshop</option>
                        <option value="Marketnuer">Marketnuer - Entrepreneur & Marketing Workshop</option>
                        <option value="Techsolve">Techsolve - Electronic - Circuts Workshop</option>
                        <option value="Data Station">Data Station - Data-Analsyis & Machine-learning Workshop</option>
                    </select>
                    <div class="error" id="first_preferenceError"></div>
                </div>

                <div class="preference-item">
                    <label for="second_preference">Second Choice <span class="required">*</span></label>
                    <select id="second_preference" name="second_preference" required>
                        <option value="">Select your second workshop</option>
                        <option value="Devology">Devology - Full stack Web-Development Workshop</option>
                        <option value="Marketnuer">Marketnuer - Entrepreneur & Marketing Workshop</option>
                        <option value="Techsolve">Techsolve - Electronic - Circuts Workshop</option>
                        <option value="Data Station">Data Station - Data-Analsyis & Machine-learning Workshop</option>
                    </select>
                    <div class="error" id="second_preferenceError"></div>
                </div>

                <div class="preference-item">
                    <label for="third_preference">Third Choice <span class="required">*</span></label>
                    <select id="third_preference" name="third_preference" required>
                        <option value="">Select your third workshop</option>
                        <option value="Devology">Devology - Full stack Web-Development Workshop</option>
                        <option value="Marketnuer">Marketnuer - Entrepreneur & Marketing Workshop</option>
                        <option value="Techsolve">Techsolve - Electronic - Circuts Workshop</option>
                        <option value="Data Station">Data Station - Data-Analsyis & Machine-learning Workshop</option>
                    </select>
                    <div class="error" id="third_preferenceError"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="tech_skills">Technical Skills (Optional)</label>
                <textarea id="tech_skills" name="tech_skills"
                    placeholder="e.g., HTML, CSS, JavaScript,Python,AI..."></textarea>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">Register for Workshops</button>
        </form>
    </div>
</body>
<script src="./assets/js/form.js" defer></script>

</html>