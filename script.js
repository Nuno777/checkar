document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('unlock-button').addEventListener('click', function() {
        var unlockCode = document.getElementById('unlock-code').value;
        var correctCode = '111'; // Replace with your unlock code

        if (unlockCode === correctCode) {
            document.querySelector('.maintenance-message').style.display = 'none';
            document.querySelector('.login-form').style.display = 'block';
        } else {
            alert('Incorrect code. Please try again.');
        }
    });
});
