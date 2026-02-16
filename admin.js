// Admin Panel JavaScript

// Function to confirm deletion
function confirmDelete(message) {
  return confirm(message || "Are you sure you want to delete this item?")
}

// Function to preview image before upload
function previewImage(input, previewId) {
  if (input.files && input.files[0]) {
    var reader = new FileReader()

    reader.onload = (e) => {
      document.getElementById(previewId).src = e.target.result
      document.getElementById(previewId).style.display = "block"
    }

    reader.readAsDataURL(input.files[0])
  }
}

// Function to toggle sidebar on mobile
document.addEventListener("DOMContentLoaded", () => {
  // Add mobile menu toggle functionality if needed
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const adminNav = document.querySelector(".admin-nav")

  if (mobileMenuToggle && adminNav) {
    mobileMenuToggle.addEventListener("click", () => {
      adminNav.classList.toggle("active")
    })
  }

  // Add active class to current nav item
  const currentPath = window.location.pathname
  const navLinks = document.querySelectorAll(".nav-links a")

  navLinks.forEach((link) => {
    const linkPath = link.getAttribute("href")
    if (currentPath.includes(linkPath) && linkPath !== "#") {
      link.classList.add("active")
    }
  })

  // Initialize any modals
  const modalTriggers = document.querySelectorAll("[data-modal-target]")
  const modalCloseButtons = document.querySelectorAll("[data-modal-close]")

  modalTriggers.forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const modalId = trigger.getAttribute("data-modal-target")
      const modal = document.getElementById(modalId)
      if (modal) {
        modal.classList.add("active")
      }
    })
  })

  modalCloseButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const modal = button.closest(".modal-overlay")
      if (modal) {
        modal.classList.remove("active")
      }
    })
  })

  // Close modal when clicking outside
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal-overlay")) {
      e.target.classList.remove("active")
    }
  })
})

