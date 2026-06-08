// Animation bouton hero
document.querySelector(".hero-btn").addEventListener("click", () => {
    window.scrollTo({ top: 500, behavior: "smooth" });
});

// Wilaya
document.getElementById("btnWilaya").addEventListener("click", () => {
    let w = document.getElementById("wilayaSelect").value;

    if (!w) return alert("Veuillez choisir une wilaya.");
    alert("Recherche des artisans de la wilaya " + w + "...");
});

// Métier + auto correct
document.getElementById("btnJob").addEventListener("click", () => {
    let job = document.getElementById("jobInput").value.trim();

    if (!job) return alert("Veuillez saisir un métier.");
    alert("Recherche des artisans : " + job);
});
