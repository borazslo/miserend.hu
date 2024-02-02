import { Controller } from '@hotwired/stimulus';

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

        const body = new FormData();
        body.set("church", this.churchId);
        if (this.favorite) {
            body.set('method', 'del');
        } else {
            body.set('method', 'add');
        }

        try {
            let response = await fetch('/api/v1/church/favorite', {
                method: 'POST',
                cache: 'no-cache',
                redirect: "follow",
                referrerPolicy: "no-referrer",
                body: body,
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
