// Script que recibe el formulario y lo envía por correo (destinatarios y copia oculta se configuran en enviar.php).
// Si se deja vacío, el formulario abre el correo del usuario con los datos ya escritos.
const FORM_ENDPOINT = "enviar.php";
const FORM_FALLBACK_EMAIL = "marketing@timeouteducation.com";

document.documentElement.classList.add("js");

// Menú móvil
const toggle = document.querySelector(".menu-toggle");
const menu = document.getElementById("menu");
if (toggle && menu) {
  const setOpen = (open) => {
    menu.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", String(open));
    toggle.querySelector("use").setAttribute("href", open ? "#i-close" : "#i-menu");
    toggle.querySelector(".sr-only").textContent = open ? "Cerrar menú" : "Abrir menú";
  };
  toggle.addEventListener("click", () => setOpen(!menu.classList.contains("is-open")));
  menu.addEventListener("click", (e) => { if (e.target.closest("a")) setOpen(false); });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && menu.classList.contains("is-open")) { setOpen(false); toggle.focus(); }
  });
}

// Borde del encabezado al hacer scroll
const header = document.querySelector(".site-header");
const onScroll = () => header.classList.toggle("is-scrolled", window.scrollY > 8);
window.addEventListener("scroll", onScroll, { passive: true });
onScroll();

// Videos de testimonios: un solo video sonando a la vez
const cards = document.querySelectorAll(".video-card");
cards.forEach((card) => {
  const video = card.querySelector("video");
  const play = card.querySelector(".video-play");
  play.addEventListener("click", () => {
    cards.forEach((other) => {
      const v = other.querySelector("video");
      if (other !== card && !v.paused) v.pause();
    });
    card.classList.add("is-playing");
    video.controls = true;
    video.focus();
    video.play().catch(() => {
      card.classList.remove("is-playing");
      video.controls = false;
    });
  });
  video.addEventListener("ended", () => {
    card.classList.remove("is-playing");
    video.controls = false;
    video.load();
  });
});

// Formulario
const form = document.getElementById("contact-form");
if (form) {
  const status = form.querySelector(".form-status");
  const submit = form.querySelector('[type="submit"]');
  const messages = {
    valueMissing: "Completa este campo.",
    typeMismatch: "Revisa el formato del correo.",
  };

  const showError = (field) => {
    const wrap = field.closest(".field");
    let error = wrap.querySelector(".field-error");
    if (field.validity.valid) {
      field.removeAttribute("aria-invalid");
      if (error) error.remove();
      return true;
    }
    if (!error) {
      error = document.createElement("p");
      error.className = "field-error";
      error.id = `${field.id}-error`;
      wrap.append(error);
      field.setAttribute("aria-describedby", error.id);
    }
    error.textContent = field.validity.valueMissing ? messages.valueMissing : messages.typeMismatch;
    field.setAttribute("aria-invalid", "true");
    return false;
  };

  form.querySelectorAll("[required]").forEach((field) => {
    field.addEventListener("blur", () => { if (field.value) showError(field); });
    field.addEventListener("input", () => { if (field.hasAttribute("aria-invalid")) showError(field); });
  });

  const setStatus = (text, kind) => {
    status.textContent = text;
    status.className = `form-status${kind ? ` is-${kind}` : ""}`;
  };

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const invalid = [...form.querySelectorAll("[required]")].filter((f) => !showError(f));
    if (invalid.length) {
      invalid[0].focus();
      setStatus("Revisa los campos marcados.", "error");
      return;
    }

    const data = new FormData(form);

    if (!FORM_ENDPOINT) {
      const body = [
        `Nombre: ${data.get("nombre")} ${data.get("apellido")}`,
        `Correo: ${data.get("email")}`,
        `Teléfono: ${data.get("telefono")}`,
        `Fecha de nacimiento del estudiante: ${data.get("fecha_nacimiento") || "—"}`,
        `Destino de interés: ${data.get("destino") || "Aún no lo sé"}`,
        "",
        data.get("mensaje") || "",
      ].join("\n");
      window.location.href = `mailto:${FORM_FALLBACK_EMAIL}?subject=${encodeURIComponent("Información high school en el extranjero")}&body=${encodeURIComponent(body)}`;
      setStatus("Abrimos tu correo con tus datos listos para enviar.", "ok");
      return;
    }

    submit.disabled = true;
    submit.textContent = "Enviando…";
    setStatus("", "");
    try {
      const res = await fetch(FORM_ENDPOINT, { method: "POST", body: data, headers: { Accept: "application/json" } });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || !json.ok) throw new Error(json.error || String(res.status));
      form.reset();
      setStatus("Recibimos tus datos. Te contactaremos pronto para agendar la llamada.", "ok");
    } catch {
      setStatus(`No se pudo enviar. Escríbenos a ${FORM_FALLBACK_EMAIL} o por WhatsApp.`, "error");
    } finally {
      submit.disabled = false;
      submit.textContent = "Quiero más información";
    }
  });
}

document.getElementById("year").textContent = new Date().getFullYear();
