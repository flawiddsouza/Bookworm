import Toastify from 'toastify-js'
import "toastify-js/src/toastify.css"

export default {
    install: (app, options) => {
        app.config.globalProperties.$snotify = {
            error(message) {
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: 'bottom',
                    position: 'right',
                    backgroundColor: 'red',
                    stopOnFocus: true, // Prevents dismissing of toast on hover
                }).showToast()
            },
            success(message) {
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: 'bottom',
                    position: 'right',
                    backgroundColor: 'green',
                    stopOnFocus: true, // Prevents dismissing of toast on hover
                }).showToast()
            }
        }
    }
}
