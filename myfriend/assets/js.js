document.addEventListener("DOMContentLoaded", function () {
    var e = document.querySelectorAll(".joe_config__aside .item"),
        t = document.querySelector(".wjssk-notice"),
        s = document.querySelector(".joe_config > form"),
        n = document.querySelectorAll(".joe_content");
    
    document.querySelector('.joe_config').parentNode.style = "margin-left: 0;width: 100%;";
    if (e.forEach(function (o) {
        o.addEventListener("click", function () {
            var c = o.getAttribute("data-current");
            if (!c) {
                return false;
            }
            e.forEach(function (e) {
                e.classList.remove("active")
            }), o.classList.add("active");
            sessionStorage.setItem("joe_config_current", c), "wjssk-notice" === c ? (t.style.display = "block", s.style.display = "none") : (t.style.display = "none", s.style.display = "block"), n.forEach(function (e) {
                e.style.display = "none";
                var t = e.classList.contains(c);
                t && (e.style.display = "block")
            })
        })
    }), sessionStorage.getItem("joe_config_current")) {
        var o = sessionStorage.getItem("joe_config_current");
        "wjssk-notice" === o ? (t.style.display = "block", s.style.display = "none") : (s.style.display = "block", t.style.display = "none"), e.forEach(function (e) {
            var t = e.getAttribute("data-current");
            t === o && e.classList.add("active")
        }), n.forEach(function (e) {
            e.classList.contains(o) && (e.style.display = "block")
        })
    } else e[0].classList.add("active"), t.style.display = "block", s.style.display = "none";
});
