// Function to fetch and display text file content
function loadTextFile() {
    fetch("./Resources/FooterElements.txt")
        .then((response) => {
            if (!response.ok) {
                throw new Error("File not found");
            }
            return response.text();
        })
        .then((text) => {
            document.getElementById("footer-content").innerHTML = text;
        })
        .catch((error) => {
            document.getElementById("footer-content").textContent =
                "_Error_: " + error.message + " [PLEASE CONTACT ADMIN]";
        });
}

function randomFooterImageSelector() {
    const num1 = Math.floor(Math.random() * 8) + 1;
    let num2;
    do {
        num2 = Math.floor(Math.random() * 8) + 1;
    } while (num2 === num1);

    document.getElementById("footer-image-1").innerHTML =
        '<img src="./logos/KU_subjects/' + num1 + '.webp" alt="Subjects Of KU">';
    document.getElementById("footer-image-2").innerHTML =
        '<img src="./logos/KU_subjects/' + num2 + '.webp" alt="Subjects Of KU">';
}

window.onload = function () {
    loadTextFile();
    randomFooterImageSelector();
};