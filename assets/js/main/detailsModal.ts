'use strict';

import * as $ from "jquery";
import * as Utils from "./utils";
import Artisan from "./Artisan";

declare var TRACKING_URL: string;

const DATA_COMPLETE_PERFECT = 100;
const DATA_COMPLETE_GREAT = 90;
const DATA_COMPLETE_GOOD = 80;
const DATA_COMPLETE_OK = 60;

function formatShortInfo(state: string, city: string, since: string, formerly: string[]) {
    let sinceTxt = since || '<i class="fas fa-question-circle" title="How long?"></i>';
    let formerlyTxt = formerly.length ? `<br />Formerly ${formerly.join(', ')}` : '';
    let location = [state, city].filter(i => i).join(', ') || '<i class="fas fa-question-circle" title="Where are you?"></i>';

    return `Based in ${location}, crafting since ${sinceTxt}${formerlyTxt}`;
}

function formatSpecies(speciesDoes: string, speciesDoesnt: string): string {
    let does = speciesDoes !== '' ? '<strong>Does:</strong> ' + speciesDoes : '';
    let doesnt = speciesDoesnt !== '' ? '<strong>Doesn\'t</strong>: ' + speciesDoesnt : '';

    if (does !== '' && doesnt !== '') {
        return `${does}<br />${doesnt}`;
    } else if (does !== '' || doesnt !== '') {
        return `${does}${doesnt}`;
    } else {
        return '<i class="fas fa-question-circle"></i>';
    }
}

function formatPaymentPlans(paymentPlans: string): string {
    return paymentPlans || '<i class="fas fa-question-circle"></i>';
}

function linksHrefNoProto($link) {
    return $link.attr('href').replace(/^https?:\/\/|\/$/g, '');
}

function formatLinks($links: any, completeness: number): string {
    $links.addClass('btn btn-light m-1').html(function (_, oldHtml) {
        return `${oldHtml} <span class="d-none d-md-inline">: <span class="url">${linksHrefNoProto($(this))}</span></span>`;
    });

    let $result = $('<div/>').append($links.length
        ? `<p class="small px-1">If you're going to contact the studio/maker, <u>please let them know you found them`
            + ` here</u>${completeness < DATA_COMPLETE_GOOD ? ' and that their data could use some updates' : ''}!`
            + ` This will help us all a lot. Thank you!</p>`
        : '<i class="fas fa-question-circle" title="None provided"></i>'
    ).append($links);

    return $result.html();
}

function htmlListFromArrays(list: String[], other: String[] = []) {
    let listLis = list.length ? `<li>${list.join('</li><li>')}</li>` : '';
    let otherLis = other.length ? `<li>Other: ${other.join('; ')}</li>` : '';

    return listLis + otherLis ? `<ul>${listLis}${otherLis}</ul>` : '<i class="fas fa-question-circle"></i>';
}

function updateCommissionsStatusFromArtisanRowData(commissionsStatusData, cstLastCheck, cstUrl) {
    let commissionsStatus = commissionsStatusData === null ? 'unknown' : commissionsStatusData ? 'open' : 'closed';
    let description;
    let parsingFailed = false;

    if (cstUrl === '') {
        description = `Commissions are <strong>${commissionsStatus}</strong>. Status is not automatically
                        tracked and updated. <a href="${TRACKING_URL}">Learn more</a>`;
    } else if (commissionsStatusData === null) {
        description = `Commissions status is unknown. It should be tracked and updated automatically from
                        this web page: <a href="${cstUrl}">${cstUrl}</a>, however the software failed to
                        "understand" the status based on the page contents. Last time it tried
                        on ${cstLastCheck} UTC. <a href="${TRACKING_URL}">Learn more</a>`;

        parsingFailed = true;
    } else {
        description = `Commissions are <strong>${commissionsStatus}</strong>. Status is tracked and updated
                        automatically from this web page: <a href="${cstUrl}">${cstUrl}</a>. Last time checked
                        on ${cstLastCheck} UTC. <a href="${TRACKING_URL}">Learn more</a>`;
    }

    $('#artisanCommissionsStatus').html(description);
    $('#statusParsingFailed').toggle(parsingFailed);
}

function getCompletenessComment(completeness: number): string {
    if (completeness === DATA_COMPLETE_PERFECT) {
        return 'Awesome! <i class="fas fa-heart"></i>';
    } else if (completeness >= DATA_COMPLETE_GREAT) {
        return 'Great!'
    } else if (completeness >= DATA_COMPLETE_GOOD) {
        return 'Good job!'
    } else if (completeness >= DATA_COMPLETE_OK) {
        return 'Some updates might be helpful...';
    } else {
        return 'Yikes! :( Updates needed!'
    }
}

function updateDetailsModalWithArtisanData(artisan: Artisan) {
    $('#artisanName').html(artisan.name + Utils.countryFlagHtml(artisan.country));
    $('#makerId')
        .html(artisan.makerId ? '<i class="fas fa-link"></i> ' + artisan.makerId : '')
        .attr('href', `#${artisan.makerId}`);
    $('#artisanShortInfo').html(formatShortInfo(artisan.state, artisan.city, artisan.since, artisan.formerly));
    $('#artisanProductionModel').html(htmlListFromArrays(artisan.productionModels));
    $('#artisanStyles').html(htmlListFromArrays(artisan.styles, artisan.otherStyles));
    $('#artisanTypes').html(htmlListFromArrays(artisan.orderTypes, artisan.otherOrderTypes));
    $('#artisanFeatures').html(htmlListFromArrays(artisan.features, artisan.otherFeatures));
    $('#artisanSpecies').html(formatSpecies(artisan.speciesDoes, artisan.speciesDoesnt));
    $('#artisanPaymentPlans').html(formatPaymentPlans(artisan.paymentPlans));
    $('#artisanLinks').html(formatLinks(Utils.getLinks$(artisan), artisan.completeness));
    $('#artisanIntro').html(artisan.intro).toggle(artisan.intro !== '');
    $('#artisanCompleteness').html(artisan.completeness.toString());
    $('#artisanCompletenessComment').html(getCompletenessComment(artisan.completeness));

    updateCommissionsStatusFromArtisanRowData(artisan.areCommissionsOpen, artisan.commissionsQuotesLastCheck,
        artisan.commissionsQuotesCheckUrl);
    Utils.updateUpdateRequestData('updateRequestFull', artisan);

    Utils.makeLinksOpenNewTab('#artisanLinks a');
    Utils.makeLinksOpenNewTab('#artisanCommissionsStatus a');
}

export function init() {
    $('#artisanDetailsModal').on('show.bs.modal', function (event: any) {
        updateDetailsModalWithArtisanData($(event.relatedTarget).closest('tr').data('artisan'));
    });

    Utils.makeLinksOpenNewTab('#updateRequestFull a');
}
