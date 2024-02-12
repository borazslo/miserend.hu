import { Controller } from '@hotwired/stimulus';
import Routing from 'fos-router';

export default class extends Controller {
    static targets = ['churchRow']
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

    favoriteDeleteButtonDidClick(event) {
        event.preventDefault()

        this.removeFavorite(event.params.churchId)
    }

    async toggle() {
        let method = 'POST'
        if (this.favorite) {
            method = 'DELETE'
        }

        let success = await this.request(method, this.churchId)

        console.log(success)

        if (success) {
            this.favorite = !this.favorite
            this.updateIcon()
        }
    }

    async removeFavorite(churchId) {
        let success = await this.request('DELETE', churchId)

        if (success) {
            this.removeFavoriteChurchRow(churchId)
        }
    }

    removeFavoriteChurch(churchId) {
        churchId *= 1

        for (let row of this.churchRowTargets) {
            if ((row.dataset.churchId*1) === churchId) {
                row.remove()
                break
            }
        }

    }

    async request(method, churchId) {
        if (this.loading) {
            return
        }

        this.loading = true

        let success = false

        try {
            let url = Routing.generate('user_favorite_change', {
                church: churchId
            })
            let response = await fetch(url, {
                method: method,
                cache: 'no-cache',
                redirect: "follow",
                referrerPolicy: "no-referrer",
            })

            let responseObject = await response.json()

            success = responseObject === 'OK'

        } catch (e) {
        }

        this.loading = false

        return success
    }
}
