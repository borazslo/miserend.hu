import { Controller } from '@hotwired/stimulus';
import Autocomplete from 'bootstrap5-autocomplete';

export default class extends Controller {
    static targets = ['diocese', 'deanDistrict', 'greekOnly', 'hasMassInLanguage', 'detailedChurchSearchButton', 'massAtDay', 'massAtTime', 'massDay', 'massTime']

    _detailedChurchSearch = false
    _dioceseRowNode = null
    _deanDistrictRowNode = null
    _greekOnlyRowNode = null
    _hasMassInLanguageRowNode = null

    connect() {
        this.initDetailedChurchSearch()

        this.findRowNodes()
    }

    findRowNodes() {
        this._dioceseRowNode = this.dioceseTarget.closest('.row')
        this._deanDistrictRowNode = this.deanDistrictTarget.closest('.row')
        this._greekOnlyRowNode = this.greekOnlyTarget.closest('.row')
        this._hasMassInLanguageRowNode = this.hasMassInLanguageTarget.closest('.row')
    }

    detailedChurchSearchButtonDidClick(e) {
        e.preventDefault()

        this.toggleChurchDetailSearch()
    }

    initDetailedChurchSearch() {
        this._detailedChurchSearch = true // todo init with dom state
    }

    get detailedChurchSearch() {
        return this._detailedChurchSearch
    }

    set detailedChurchSearch(value) {
        this._detailedChurchSearch = value
    }

    toggleChurchDetailSearch() {
        if (this.detailedChurchSearch) {
            this.hideDetailChurchSearch()
        } else {
            this.showDetailChurchSearch()
        }
    }

    hideDetailChurchSearch() {
        if (!this.detailedChurchSearch) {
            return
        }

        this._dioceseRowNode.classList.add('d-none')
        this._deanDistrictRowNode.classList.add('d-none')
        this._greekOnlyRowNode.classList.add('d-none')
        this._hasMassInLanguageRowNode.classList.add('d-none')

        this.detailedChurchSearchButtonTarget.innerHTML = 'Részletes keresés'
        
        this.resetChurchFields()

        this.detailedChurchSearch = false
    }

    showDetailChurchSearch() {
        if (this.detailedChurchSearch) {
            return
        }

        this._dioceseRowNode.classList.remove('d-none')
        this._deanDistrictRowNode.classList.remove('d-none')
        this._greekOnlyRowNode.classList.remove('d-none')
        this._hasMassInLanguageRowNode.classList.remove('d-none')

        this.detailedChurchSearchButtonTarget.innerHTML = 'Részletes keresés elrejtése'

        this.detailedChurchSearch = true
    }

    resetChurchFields() {
        this.dioceseTarget.value = '0'
        for (let field of this.deanDistrictTargets) {
            field.value = '0'
        }
        this.greekOnlyTarget.checked = false
        this.hasMassInLanguageTarget.value = '0'
    }

    massAtDaySelectDidChange(e) {
        this.updateMassAtDayDateField()
    }

    updateMassAtDayDateField() {
        if (this.massAtDayTarget.value === 'x') {
            this.massAtDayTarget.classList.remove('rounded-1')
            this.massDayTarget.classList.remove('d-none')
        } else {
            this.massAtDayTarget.classList.add('rounded-1')
            this.massDayTarget.classList.add('d-none')
        }
    }

    massAtTimeSelectDidChange(e) {
        this.updateMassAtTimeField()
    }

    updateMassAtTimeField() {
        if (this.massAtTimeTarget.value === 'x') {
            this.massAtTimeTarget.classList.remove('rounded-1')
            this.massTimeTarget.classList.remove('d-none')
        } else {
            this.massAtTimeTarget.classList.add('rounded-1')
            this.massTimeTarget.classList.add('d-none')
        }
    }
}