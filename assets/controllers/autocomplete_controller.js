import { Controller } from '@hotwired/stimulus';
import Autocomplete from 'bootstrap5-autocomplete';

export default class extends Controller {
    connect() {
        this.endpoint = this.element.dataset.endpoint
        this.autocomplete = new Autocomplete(this.element, {
            suggestionsThreshold: 2,
            preventBrowserAutocomplete: true,
            server: this.endpoint,
            serverMethod: 'GET',
            liveServer: true,
            onServerResponse: async function (response, inst) {
                let json = await response.json()

                console.log(json)

                let result = json.results.map(item => {
                    return {
                        label: item.label,
                        value: item.value
                    }
                })

                console.log(result)

                return result
            }
        })
    }
}