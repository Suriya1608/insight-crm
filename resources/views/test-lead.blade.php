<!DOCTYPE html>
<html>
<head>
    <title>Test Lead Form</title>
</head>
<body>

<h2>Test Lead Form</h2>

<form id="leadForm">
    <input type="text" id="name" placeholder="Name" required><br><br>
    <input type="text" id="phone" placeholder="Phone" required><br><br>
    <input type="email" id="email" placeholder="Email"><br><br>
    <input type="text" id="course" placeholder="Course"><br><br>

    {{-- Hidden UTM / Meta ad tracking fields — populated from URL params by JS --}}
    <input type="hidden" id="utm_source">
    <input type="hidden" id="utm_medium">
    <input type="hidden" id="utm_campaign">
    <input type="hidden" id="utm_content">
    <input type="hidden" id="utm_term">
    <input type="hidden" id="fbclid">

    <button type="submit">Submit</button>
</form>

<script>
// Read UTM params and fbclid from the URL and populate hidden fields
(function () {
    const params = new URLSearchParams(window.location.search);
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid'].forEach(function (key) {
        const val = params.get(key);
        if (val) {
            const el = document.getElementById(key);
            if (el) el.value = val;
        }
    });
})();

document.getElementById("leadForm").addEventListener("submit", function(e) {

    e.preventDefault();

    fetch("http://127.0.0.1:8000/crm-store-lead", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-API-KEY": "college_secure_2025_key"
        },
        body: JSON.stringify({
            name:         document.getElementById("name").value,
            phone:        document.getElementById("phone").value,
            email:        document.getElementById("email").value,
            course:       document.getElementById("course").value,
            utm_source:   document.getElementById("utm_source").value || null,
            utm_medium:   document.getElementById("utm_medium").value || null,
            utm_campaign: document.getElementById("utm_campaign").value || null,
            utm_content:  document.getElementById("utm_content").value || null,
            utm_term:     document.getElementById("utm_term").value || null,
            fbclid:       document.getElementById("fbclid").value || null,
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(JSON.stringify(data));
        console.log(data);
    })
    .catch(error => {
        console.error("Error:", error);
    });

});
</script>

</body>
</html>
