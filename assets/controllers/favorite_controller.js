import { Controller } from '@hotwired/stimulus';
import Autocomplete from 'bootstrap5-autocomplete';

export default class extends Controller {
    loading = false

    connect() {
        this.initStatus()
    }

    initStatus() {
        this.favorite = this.element.classList.contains('text-warning')
    }

    updateIcon() {
        if (this.favorite) {
            this.element.classList.add('text-warning')
        } else {
            this.element.classList.remove('text-warning')
        }
    }

    get churchId() {
        return this.element.dataset.churchId * 1
    }

    favoriteButtonDidClick(event) {
        event.preventDefault()

        this.toggle()
    }

    async toggle() {
        if (this.loading) {
            return
        }

        this.loading = true

        let body
        if (this.favorite) {
            body = {
                method: 'del',
                'church-id': this.churchId
            }
        } else {
            body = {
                method: 'add',
                'church-id': this.churchId
            }
        }

        try {
            let response = await fetch('/ajax/favorite', {
                method: 'POST',
                cache: 'no-cache',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                },
                redirect: "follow",
                referrerPolicy: "no-referrer",
                body: JSON.stringify(body),
            })

            let responseObject = await response.json()


            if (responseObject === 'OK') {
                this.favorite = !this.favorite
                this.updateIcon()
            }

        } catch (e) {
        }

        this.loading = false
    }
}
