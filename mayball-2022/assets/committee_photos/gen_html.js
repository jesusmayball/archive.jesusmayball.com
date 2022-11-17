const fs = require("fs");
const readdir = fs.readdir;

readdir("2022_/assets/committee_photos", (err, files) => {
    for (let file of files) {
        if (file.endsWith(".webp")) {
            const name = file.substring(0, file.length - 4);
            const html = `<div class="committee-member">
        <img src="assets/committee_photos/${file}" alt="${name}" />
        <h2>${name}</h2>
        <h3>ROLE</h3>
        <a href="mailto:EMAIL@jesusmayball.com">EMAIL@jesusmayball.com</a>
    </div>`
            console.log(html);
        }
    }
});