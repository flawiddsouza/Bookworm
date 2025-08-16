import Toastify from 'toastify-js'
import "toastify-js/src/toastify.css"

export default {
    install: (app, options) => {
        app.config.globalProperties.$snotify = {
            info(message) {
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: 'bottom',
                    position: 'right',
                    backgroundColor: '#2196F3',
                    stopOnFocus: true, // Prevents dismissing of toast on hover
                }).showToast()
            },
            error(message) {
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: 'bottom',
                    position: 'right',
                    backgroundColor: '#f44336',
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
                    backgroundColor: '#4caf50',
                    stopOnFocus: true, // Prevents dismissing of toast on hover
                }).showToast()
            }
        }
    }
}
