// You can add JavaScript for interactive features
// For example, handling the address input and discover button click

document.querySelector('.address-input button').addEventListener('click', function() {
    const address = document.querySelector('.address-input input').value;
    alert('Discovering restaurants near: ' + address);
   });


