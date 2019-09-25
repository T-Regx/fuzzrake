'use strict';

import * as $ from 'jquery';
import * as DataTable from './dataTable';
import * as DetailsModal from './detailsModal';
import * as Utils from './utils'
import Artisan from './Artisan';

require('../../3rd-party/flag-icon-css/css/flag-icon.css');

function initRequestUpdateModal() {
    $('#updateRequestModal').on('show.bs.modal', function (event) {
        updateRequestUpdateModalWithRowData($(event.relatedTarget).closest('tr').data('artisan'));
    });

    Utils.makeLinksOpenNewTab('#updateRequestModal a');
}

function addReferrerRequestTooltip() {
    $('div.artisan-links')
        .attr('title', 'If you\'re going to contact the studio/maker, <u>please let them' +
            ' know you found them here</u>! This will help us all a lot. Thank you!')
        .data('placement', 'top')
        .data('boundary', 'window')
        .data('html', true)
        .data('fallbackPlacement', [])
        .tooltip();
}

function updateRequestUpdateModalWithRowData(artisan) {
    $('#artisanNameUR').html(artisan.name);

    Utils.updateUpdateRequestData('updateRequestSingle', artisan);
}

function openArtisanByFragment(hash) {
    if (hash) {
        $(hash).children().eq(0).trigger('click');
    }
}

export function init() {
    $('#scam-risk-acknowledgement').on('click', (event) => {
        $('#scam-risk-warning, #scam-risk-acknowledged').toggle();
        event.preventDefault();
    });

    DataTable.init();
    DetailsModal.init();

    initRequestUpdateModal();
    addReferrerRequestTooltip();
    Utils.makeLinksOpenNewTab('#artisans a:not(.request-update)');

    openArtisanByFragment(window.location.hash);

    $('#data-loading-message, #data-table-container').toggle();
}

export {Artisan};
