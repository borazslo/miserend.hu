import { Controller } from '@hotwired/stimulus';
import Autocomplete from 'bootstrap5-autocomplete';

export default class extends Controller {
    loading = false

    connect() {
        this.initStatus()
//         $(document).on('click','#star',function(){
//             var $this= $(this);
//
//             if($(this).hasClass('grey')) var method = 'add';
//             else var method = 'del';
//             var tid = $(this).attr("data-tid");
//
//             $.ajax({
//                 type:"POST",
//                 url:"/ajax/favorite",
//                 data:"tid="+tid+"&method="+method,
//                 success:function(response){
//                     $("#star").toggleClass("grey yellow");
//                     if($("#star").hasClass('grey')) $("#star").attr('title', 'Kattintásra hozzáadás a kedvencekhez.');
//                     else $("#star").attr('title', 'Kattintásra törlés a kedvencek közül.');
//                 },
//             });
//         });
    }

    initStatus() {
        this.favorite = this.element.classList.contains('favorite-status-liked')
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
                tid: this.churchId
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

    updateIcon() {
        if (this.favorite) {
            this.element.classList.add('favorite-status-liked')
            this.element.classList.remove('favorite-status-not-liked')
        } else {
            this.element.classList.remove('favorite-status-liked')
            this.element.classList.add('favorite-status-not-liked')
        }
    }
}
