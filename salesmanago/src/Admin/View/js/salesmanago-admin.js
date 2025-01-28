/* jshint esversion: 6 */
document.addEventListener( 'DOMContentLoaded', appendSalesmanagoSubmenu, false );
function appendSalesmanagoSubmenu() {
	let smMenu = document.getElementById( 'adminmenu' );
	if ( ! smMenu) {
		return;
	}
	let salesmanagoMenu = smMenu.getElementsByClassName( 'toplevel_page_salesmanago' )[0];
	if ( ! salesmanagoMenu) {
		return;
	}
	let salesmanagoSubmenu = salesmanagoMenu.getElementsByClassName( 'wp-submenu' )[0];
	if ( ! salesmanagoSubmenu) {
		return;
	}
	let salesmanagoLink = salesmanagoSubmenu.lastChild.children[0];
	if ( ! salesmanagoLink) {
		return;
	}
	salesmanagoLink.href   = "https://www.salesmanago.com/login.htm?utm_source=integration&utm_medium=wordpress&utm_content=left_menu";
	salesmanagoLink.target = "_blank";
}

function salesmanagoToggleCronFrequency()
{
	const checkbox = document.getElementById('salesmanago-cron');
	const dropdown = document = document.getElementById('cron-select-wrapper');

	dropdown.style.display = checkbox.checked ? 'block' : 'none';
}

function salesmanagoRemoveForm(id)
{
	let smFormTable = document.getElementById( "salesmanago-form-" + id );
	if ( ! smFormTable) {
		return;
	}
	smFormTable.remove();
}

function salesmanagoToggleDoubleOptIn()
{
	let smDoubleOptInFields = document.getElementsByClassName( "salesmanago-double-opt-in" );
	for (let i = 0; i < smDoubleOptInFields.length; i++) {
		smDoubleOptInFields[i].classList.toggle( "hidden" );
	}
}

function salesmanagoToggleEndpoint()
{
	let smEndpointField   = document.getElementById( "endpoint" );
	let smEndpointVisible = document.getElementById( 'salesmanago-endpoint-active' ).checked;
	if (smEndpointVisible) {
		smEndpointField.classList.remove( "hidden" );
	} else {
		smEndpointField.classList.add( "hidden" );
	}
}

function salesmanagoChangeOptInInput()
{
	let smMode = document.getElementById( 'salesmanago-opt-in-input-mode' ).value;
	document.getElementById( 'salesmanago-opt-in-input-map' ).classList.add( 'hidden' );
	document.getElementById( 'salesmanago-opt-in-input-append' ).classList.add( 'hidden' );
	document.getElementById( 'salesmanago-opt-in-input-append-info' ).classList.add( 'hidden' );
	if (smMode === 'map') {
		document.getElementById( 'salesmanago-opt-in-input-map' ).classList.remove( 'hidden' );
	} else if (smMode === 'append' || smMode === 'appendEverywhere') {
		document.getElementById( 'salesmanago-opt-in-input-append' ).classList.remove( 'hidden' );
		document.getElementById( 'salesmanago-opt-in-input-append-info' ).classList.remove( 'hidden' );
	}
}

function salesmanagoChangeOptInMobileInput()
{
	let smMode = document.getElementById( 'salesmanago-opt-in-mobile-input-mode' ).value;
	document.getElementById( 'salesmanago-opt-in-mobile-input-map' ).classList.add( 'hidden' );
	document.getElementById( 'salesmanago-opt-in-mobile-input-append' ).classList.add( 'hidden' );
	document.getElementById( 'salesmanago-opt-in-mobile-input-append-info' ).classList.add( 'hidden' );
	if (smMode === 'map') {
		document.getElementById( 'salesmanago-opt-in-mobile-input-map' ).classList.remove( 'hidden' );
	} else if (smMode === 'append' || smMode === 'appendEverywhere') {
		document.getElementById( 'salesmanago-opt-in-mobile-input-append' ).classList.remove( 'hidden' );
		document.getElementById( 'salesmanago-opt-in-mobile-input-append-info' ).classList.remove( 'hidden' );
	}
}

function salesmanagoExport()
{
	type 	= window.salesmanago.type;
	ajaxDir = window.salesmanago.ajaxDir;
	nonce 	= window.salesmanago.exportNonce;

	document.getElementById( 'salesmanago-export-forms' ).style.display  = 'none';
	document.getElementById( 'salesmanago-export-notice' ).style.display = 'block';

	salesmanagoResetView();
	salesmanagoPrepareExport( salesmanagoExportPackage );

	return false;
}

function _t(text)
{
	return window.salesmanago.translations[text] ? window.salesmanago.translations[text] : text;

}

function salesmanagoUpdateCountDiv(type, data)
{
	let smResultDiv = document.getElementById('salesmanago-count-' + type + '-result')
	if (data) {
		let smResultData = JSON.parse(data);
		smResultDiv.innerHTML = smResultData.count;
	} else {
		smResultDiv.innerHTML = '';
	}
}

function salesmanagoExportCount(type)
{
	let smXhttp  = new XMLHttpRequest();
	let smFormData = new FormData();
	let smData = '';

	salesmanagoUpdateCountDiv(type, '')
	if (type === 'contacts') {
		smFormData.append('action', 'salesmanago_export_count_contacts');
		smData = btoa(
			JSON.stringify(
				{
					dateFrom: document.getElementById( 'salesmanago-export-contacts-from' ).value,
					dateTo: document.getElementById( 'salesmanago-export-contacts-to' ).value,
					tags: document.getElementById( 'salesmanago-export-tags' ).value,
				}
			)
		);
	} else if (type === 'events') {
		smFormData.append('action', 'salesmanago_export_count_events');
		let smStatusesArray = [];
		for (let checkbox of document.getElementsByName('salesmanago-export-events-statuses[]')) {
			if (checkbox.checked) {
				smStatusesArray.push(checkbox.value);
			}
		}
		let smStatuses = smStatusesArray.join(',');
		smData = btoa(
			JSON.stringify(
				{
					dateFrom: document.getElementById( 'salesmanago-export-events-from' ).value,
					dateTo: document.getElementById( 'salesmanago-export-events-to' ).value,
					identifierType: document.getElementById( 'salesmanago-product-identifier-type' ).value,
					statuses: smStatuses,
					exportAs: document.getElementById('salesmanago-export-events-advanced-option-export-as').value,
				}
			)
		);
	} else {
		console.log( 'Wrong export type: ' + type );
	}

	smFormData.append('data', smData);

	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200) {
			salesmanagoUpdateCountDiv(type, this.responseText);
		} else if (this.readyState === 4 && this.status !== 200) {
			console.warn('Couldn\'t do ajax call')
		}
	};
	smXhttp.open( "POST", ajaxurl, true );
	smXhttp.send( smFormData );
}

function salesmanagoPrepareExport(callback)
{
	let smType    = window.salesmanago.type;
	let smAjaxDir = window.salesmanago.ajaxDir;
	let smNonce   = window.salesmanago.exportNonce;
	let smXhttp    = new XMLHttpRequest();
	let smFormData = new FormData();
	smFormData.append( 'nonce', smNonce );
	let smData = '';
	if (smType === 'contacts') {
		smFormData.append( 'action', 'salesmanago_export_count_contacts' );
		smData = btoa(
			JSON.stringify(
				{
					dateFrom: document.getElementById( 'salesmanago-export-contacts-from' ).value,
					dateTo: document.getElementById( 'salesmanago-export-contacts-to' ).value,
					tags: document.getElementById( 'salesmanago-export-tags' ).value,
				}
			)
		);
	} else if (smType === 'events') {
		smFormData.append( 'action', 'salesmanago_export_count_events' );
		let smStatusesArray = [];
		for (let checkbox of document.getElementsByName('salesmanago-export-events-statuses[]')) {
			if (checkbox.checked) {
				smStatusesArray.push(checkbox.value);
			}
		}
		let smStatuses = smStatusesArray.join(',');
		smData = btoa(
			JSON.stringify(
				{
					dateFrom: document.getElementById( 'salesmanago-export-events-from' ).value,
					dateTo: document.getElementById( 'salesmanago-export-events-to' ).value,
					identifierType: document.getElementById( 'salesmanago-product-identifier-type' ).value,
					statuses: smStatuses,
					exportAs: document.getElementById('salesmanago-export-events-advanced-option-export-as').value,
				}
			)
		);
	} else {
		console.log('Wrong export type: ' + smType);
	}
	smFormData.append( 'data', smData );
	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200) {
			window.localStorage.setItem( 'salesmanagoExport', this.responseText );
			salesmanagoUpdateView();
			callback();
		} else if (this.readyState === 4 && this.status !== 200) {
			salesmanagoHandleFailedExport( this );
		}
	};
	smXhttp.open( "POST", smAjaxDir, true );
	smXhttp.send( smFormData );
}

function salesmanagoHandleFailedExport(response)
{
	let smExportStatus = {};
	if (window.localStorage.getItem( 'salesmanagoExport' )) {
		smExportStatus = JSON.parse( window.localStorage.getItem( 'salesmanagoExport' ) );
	}
	smExportStatus.status   = 'failed';
	smExportStatus.message  = 'Unhandled error. Refresh page to resume export. ';
	smExportStatus.message += '\nTo debug this error you can edit wp-includes/load.php. Search for "XMLRPC_REQUEST" and change "display_errors" to 1.';
	smExportStatus.message += '\nStatus: ' + response.status;
	smExportStatus.message += '\nStatusText: ' + response.statusText;
	smExportStatus.message += '\nResponseText: ' + response.responseText;
	smExportStatus.message += '\nResponseURL: ' + response.responseURL;
	smExportStatus.message += '\nTimeout: ' + response.timeout;

	let smJson = JSON.stringify( smExportStatus );

	window.localStorage.setItem( 'salesmanagoExport', smJson );
	salesmanagoUpdateView();
}

function salesmanagoExportPackage()
{
	let smType    = window.salesmanago.type;
	let smAjaxDir = window.salesmanago.ajaxDir;
	let smNonce   = window.salesmanago.exportNonce;

	var smXhttp    = new XMLHttpRequest();
	var smFormData = new FormData();
	if (smType === 'contacts') {
		smFormData.append( 'action', 'salesmanago_export_contacts' );
	} else if (smType === 'events') {
		smFormData.append( 'action', 'salesmanago_export_events' );
	} else {
		console.log( 'wrong ' + smType );
	}
	smFormData.append( 'data', btoa( window.localStorage.getItem( 'salesmanagoExport' ), true ) );
	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200) {
			window.localStorage.setItem( 'salesmanagoExport', this.responseText );

			salesmanagoUpdateView();

			let smExportStatus = JSON.parse( window.localStorage.getItem( 'salesmanagoExport' ) );
			if (smExportStatus.status === "in_progress" || smExportStatus.status === "last_check") {
				salesmanagoExportPackage();
			} else {
				salesmanagoExportDone();
			}

		} else if (this.readyState === 4 && this.status !== 200) {
			salesmanagoHandleFailedExport( this );
		}
	};
	smXhttp.open( "POST", smAjaxDir, true );
	smXhttp.send( smFormData );
}

function salesmanagoExportDone()
{
	salesmanagoUpdateView();
}

function salesmanagoUpdateView()
{
	let smType    = window.salesmanago.type;
	let smAjaxDir = window.salesmanago.ajaxDir;
	let smNonce   = window.salesmanago.exportNonce;

	let smExportStatus = JSON.parse( window.localStorage.getItem( 'salesmanagoExport' ) );
	if ( ! smExportStatus) {
		return;
	}
	document.getElementById( "salesmanago-export-status" ).innerHTML = _t( smExportStatus.status );

	if (smExportStatus.status === "in_progress" || smExportStatus.status === "last_check") {
		salesmanagoSetNoticeType( 'notice-info' );
		let smPercentage = Math.round( 100 * (smExportStatus.lastExportedPackage + 1) / (smExportStatus.packageCount) );
		document.getElementById( 'salesmanago-export-progress' ).value = smPercentage;
		if (smExportStatus.status === "in_progress") {
			document.getElementById( "salesmanago-export-status" ).innerHTML += " (" + smPercentage + "%)";
		}
	} else if (smExportStatus.status === "done" || smExportStatus.status === 'no_data') {
		salesmanagoSetNoticeType( 'notice-success' );
		document.getElementById( 'salesmanago-export-progress' ).value      = 100;
		document.getElementById( 'salesmanago-export-forms' ).style.display = "block";
	} else if (smExportStatus.status === "failed") {
		salesmanagoSetNoticeType( 'notice-error' );
		if (smExportStatus.message) {
			console.log( smExportStatus.message );
		}
	} else if (smExportStatus.status !== "preparing") {
		salesmanagoSetNoticeType( 'notice-error' );
	}
}

function salesmanagoResetView()
{
	document.getElementById( 'salesmanago-export-forms' ).style.display   = 'none';
	document.getElementById( 'salesmanago-export-restore' ).style.display = 'none';
	document.getElementById( 'salesmanago-export-notice' ).style.display  = 'block';
	document.getElementById( "salesmanago-export-status" ).innerHTML      = _t( "starting" );
	document.getElementById( 'salesmanago-export-progress' ).value        = 0;
	salesmanagoSetNoticeType( 'notice-info' );
}

function salesmanagoSetNoticeType(type = 'notice-warning')
{
	let smNotice       = document.getElementById( 'salesmanago-export-notice-type' );
	smNotice.className = '';
	smNotice.classList.add( 'notice' );
	smNotice.classList.add( 'inline' );
	smNotice.classList.add( type );
	if (type === 'notice-success') {
		smNotice.classList.add( 'is-dismissible' );
	}
}

function salesmanagoExportCheckIfInterrupted()
{
	if ( ! window.salesmanago || ! window.salesmanago.isExportPage || ! window.localStorage.getItem( 'salesmanagoExport' )) {
		return;
	}
	let smExportStatus = JSON.parse( window.localStorage.getItem( 'salesmanagoExport' ) );
	if ( ! smExportStatus) {
		return;
	}
	if (smExportStatus.status === 'in_progress'
		|| smExportStatus.status === 'failed'
		|| smExportStatus.status === 'unknown'
	) {
		document.getElementById( 'salesmanago-export-restore' ).style.display = 'block';

		let smPercentage = Math.round( 100 * (smExportStatus.lastExportedPackage + 1) / (smExportStatus.packageCount) );

		let smDetails                               = document.getElementById( 'salesmanago-export-details' ).children[0];
		smDetails.children[0].children[1].innerHTML = _t( smExportStatus.type );
		smDetails.children[1].children[1].innerHTML = timeConverter( smExportStatus.started );
		smDetails.children[2].children[1].innerHTML = timeConverter( smExportStatus.lastSuccess );
		smDetails.children[3].children[1].innerHTML = smPercentage + '%';
		smDetails.children[4].children[1].innerHTML = smExportStatus.tags;

	}
}
window.addEventListener( 'load', salesmanagoExportCheckIfInterrupted );

function salesmanagoContinueExport()
{
	salesmanagoResetView();

	let smExportStatus      = JSON.parse( window.localStorage.getItem( 'salesmanagoExport' ) );
	window.salesmanago.type = smExportStatus.type;

	salesmanagoExportPackage();

	return false;
}

function salesmanagoAbortExport()
{
	document.getElementById( 'salesmanago-export-notice' ).style.display = 'none';
	window.localStorage.removeItem( 'salesmanagoExport' );
}

function timeConverter(UNIX_timestamp){
	let smDateObject = new Date( UNIX_timestamp * 1000 );
	let smLocalTime  = new Date( smDateObject.setTime( smDateObject.getTime() - smDateObject.getTimezoneOffset() * 60 * 1000 ) );
	return smLocalTime.toISOString().replace( "T", ' ' ).replace( ".000Z", '' );
}

function salesmanagoExportContacts()
{
	window.localStorage.setItem( 'salesmanagoExport', null );

	window.salesmanago.type = 'contacts';
	salesmanagoExport();
	return false;
}

function salesmanagoExportEvents()
{
	document.documentElement.scrollTop = 0;

	window.localStorage.setItem( 'salesmanagoExport', null );

	window.salesmanago.type = 'events';
	salesmanagoExport();
	return false;
}

// ---- Product Export

function salesmanagoLaunchProductExport( event )
{
	event.preventDefault();
	window.localStorage.setItem( 'salesmanagoProductExport', null );
	window.salesmanago.type = 'products';
	salesmanagoExportProducts( salesmanagoExportProductPackage );
	salesmanagoShowProductExportProgressBar();
	salesmanagoToggleProductExportBtns();
	return false;
}

function salesmanagoExportProducts( callback )
{
	let smAjaxDir  = window.ajaxurl;
	let smXhttp    = new XMLHttpRequest();
	let smFormData = new FormData();
	document.getElementById( 'sm-product-export-summary' ).setAttribute( 'hidden', '' );
	let smData     = btoa(
		JSON.stringify(
			{
				isFirstRequest: true
			}
		)
	);
	smFormData.append( 'action', 'salesmanago_export_products' );
	smFormData.append( 'data', smData );
	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200) {
			window.localStorage.setItem( 'salesmanagoProductExport', this.responseText );
			callback();
		} else if (this.readyState === 4 && this.status !== 200) {
			salesmanagoUpdateProductView();
		}
	};
	smXhttp.open( 'POST', smAjaxDir, true );
	smXhttp.send( smFormData );
}

function salesmanagoExportProductPackage()
{
	let smType     = 'products';
	let smAjaxDir  = window.ajaxurl;
	var smXhttp    = new XMLHttpRequest();
	var smFormData = new FormData();

	smFormData.append( 'action', 'salesmanago_export_products' );
	smFormData.append( 'data', btoa( window.localStorage.getItem( 'salesmanagoProductExport' ), true ) );
	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200) {
			window.localStorage.setItem( 'salesmanagoProductExport', this.responseText );
			salesmanagoUpdateProductView();
			let smExportStatus = JSON.parse( window.localStorage.getItem( 'salesmanagoProductExport' ) );
			if (smExportStatus.status === "in_progress") {
				salesmanagoExportProductPackage();
			} else {
				salesmanagoToggleProductExportBtns();
			}
		} else if (this.readyState === 4 && this.status !== 200) {
			salesmanagoUpdateProductView();
		}
	};
	smXhttp.open( "POST", smAjaxDir, true );
	smXhttp.send( smFormData );
}

function salesmanagoUpdateProductView()
{
	const smExportStatus = JSON.parse( window.localStorage.getItem( 'salesmanagoProductExport' ) );
	if ( ! smExportStatus ) {
		return;
	}
	document.getElementById( "sm-product-export-status" ).innerHTML = _t( smExportStatus.status );
	if ( smExportStatus.status === "in_progress" ) {
		salesmanagoSetProductExportNoticeType( 'notice-info' );
		let smPercentage = Math.round( 100 * ( smExportStatus.lastExportedPackage + 1 ) / ( smExportStatus.packageCount ) );
		document.getElementById( 'sm-product-export-progress' ).value = smPercentage;
		if ( smExportStatus.status === "in_progress" ) {
			document.getElementById( "sm-product-export-status" ).innerHTML += " (" + smPercentage + "%)";
		}
	} else if ( smExportStatus.status === "done" || smExportStatus.status === 'no_data' ) {
		salesmanagoSetProductExportNoticeType( 'notice-success' );
		document.getElementById( 'sm-product-export-progress' ).value = 100;
		salesmanagoShowProductExportSummary();
	} else if ( smExportStatus.status === "failed" ) {
		salesmanagoSetProductExportNoticeType( 'notice-error' );
		if ( smExportStatus.message === 'Expired API Key. Refresh the page and add a new API key') {
			console.warn( _t( 'expired_api_key' ) );
		} else {
			console.log( _t( smExportStatus.message ) );
			salesmanagoShowProductExportSummary();
		}
	} else if ( smExportStatus.status !== "preparing" ) {
		salesmanagoSetProductExportNoticeType( 'notice-error' );
	}
}

function salesmanagoShowProductExportProgressBar( completion = 0 )
{
	if (document.getElementById( 'sm-product-export-summary' )) {
		document.getElementById( 'sm-product-export-summary' ).hidden = true;
	}
	document.getElementById( 'sm-product-export-notice' ).style.display = 'block';
	document.getElementById( 'sm-product-export-status' ).innerHTML     = _t( 'starting' );
	document.getElementById( 'sm-product-export-notice-type' ).classList.remove( 'notice-error' );
	document.getElementById( 'sm-product-export-notice-type' ).classList.add( 'notice-info' );
	document.getElementById( 'sm-product-export-progress' ).value       = completion;
}

function salesmanagoSetProductExportNoticeType( type = 'notice-warning' )
{
	let smNotice       = document.getElementById( 'sm-product-export-notice-type' );
	smNotice.className = '';
	smNotice.classList.add( 'notice' );
	smNotice.classList.add( 'inline' );
	smNotice.classList.add( type );
	if (type === 'notice-success' ) {
		smNotice.classList.add( 'is-dismissible' );
	}
}

function salesmanagoToggleProductExportBtns()
{
	document.getElementById( 'sm-btn-product-export' ).style.display = 'none';
	document.getElementById( 'sm-btn-set-active-catalog' ).toggleAttribute( 'disabled' );
	document.getElementById( 'sm-btn-product-export-try-again' ).toggleAttribute( 'disabled' );
}

function salesmanagoShowProductExportSummary()
{
	let smProductExportSummary = JSON.parse( window.localStorage.getItem( 'salesmanagoProductExport' ) );
	document.getElementById( 'sm-product-export-summary' ).removeAttribute( 'hidden' );
	if (smProductExportSummary.status === 'failed') {
		document.getElementById( 'sm-product-export-summary' ).innerHTML               = _t( 'packages_exported' ) + (smProductExportSummary.lastExportedPackage + 1) + ' / ' + smProductExportSummary.packageCount;
		document.getElementById( 'sm-continue-product-exp-btn-wrapper' ).style.display = 'inline-block';
	} else {
		document.getElementById( 'sm-continue-product-exp-btn-wrapper' ).style.display = 'none';
		document.getElementById( 'sm-product-export-summary' ).innerHTML               = _t( 'products_exported' ) + smProductExportSummary.count + '<br>';
	}
}
// Show warning when choosing 'None' catalog for synchro
function salesmanagoShowModal() {
	const selectedElem = document.getElementById( 'sm-product-catalog-select' );
	if ( selectedElem.options[ selectedElem.selectedIndex ].value === "" ) {
		document.getElementById( 'sm-anchor-open-warning-modal' ).click();
	}
}

function salesmanagoCheckForInterruptedProductExport()
{
	let smExportData = JSON.parse( window.localStorage.getItem( 'salesmanagoProductExport' ) );
	if (smExportData && smExportData.status === 'failed') {
		document.getElementById( 'sm-notice-product-exp-interrupted-wrapper' ).style.display = 'block';
		document.getElementById( 'sm-btn-product-export' ).style.display                        = 'none';
		const smExpDetails                             = document.getElementById( 'sm-product-exp-interrupted-summary' );
		let smPercentage                               = Math.round( 100 * (smExportData.lastExportedPackage + 1) / (smExportData.packageCount) );
		const lastExportedPackage                      = smExportData.lastExportedPackage !== -1 ? smExportData.lastExportedPackage : 0; // For display
		smExpDetails.children[0].children[1].innerHTML = timeConverter( smExportData.started );
		smExpDetails.children[1].children[1].innerHTML = timeConverter( smExportData.lastSuccess );
		smExpDetails.children[2].children[1].innerHTML = lastExportedPackage;
		smExpDetails.children[3].children[1].innerHTML = smPercentage + '%';
	}
}

function salesmanagoContinueProductExport( event )
{
	event.preventDefault()
	document.getElementById( 'sm-notice-product-exp-interrupted' ).style.display = 'none';
	let smUnfinishedExportData = JSON.parse( window.localStorage.getItem( 'salesmanagoProductExport' ) );
	if (smUnfinishedExportData) {
		smUnfinishedExportData.status  = 'in_progress';
		smUnfinishedExportData.message = null;
		window.localStorage.setItem( 'salesmanagoProductExport', JSON.stringify( smUnfinishedExportData ) );
		const exportCompletion         = Math.round( 100 * ( smUnfinishedExportData.lastExportedPackage + 1 ) / ( smUnfinishedExportData.packageCount ) );
		salesmanagoShowProductExportProgressBar( exportCompletion );
		salesmanagoExportProductPackage();
		salesmanagoToggleProductExportBtns();
	}
}

function salesmanagoAbortProductExport( event )
{
	event.preventDefault();
	document.getElementById( 'sm-notice-product-exp-interrupted' ).style.display = 'none';
	salesmanagoClearInterruptedExportData();
	document.getElementById( 'sm-btn-product-export' ).style.display = 'inline-block';
}

function salesmanagoClearInterruptedExportData()
{
	window.localStorage.removeItem( 'salesmanagoProductExport' );
}
// ----- END of Product Export

function salesmanagoTurnOffCatalogSynchro()
{
	salesmanagoClearInterruptedExportData();
	document.getElementById( 'sm-btn-set-active-catalog' ).click();
}

function salesmanagoCheckDoi()
{
	const smDoiValid = salesmanagoValidateDoiEmailId();

	const input = document.getElementById('double-opt-in-email-id');
	const errorMessage = document.getElementById('salesmanago-doi-input-error');
	const warningMessage = document.getElementById('salesmanago-doi-input-warning');

	const saveBtn = document.getElementById('salesmanago-save-btn');

	if (!smDoiValid) {
		input.classList.add('text-input-validation-error');
		errorMessage.classList.remove('hidden');
		warningMessage.classList.add('hidden');
		saveBtn.setAttribute('disabled', '');
	} else {
		input.classList.remove('text-input-validation-error');
		errorMessage.classList.add('hidden');
		saveBtn.removeAttribute('disabled');
		if ( salesmanagoIsDoiEmailIdTemplateId() )	{
			input.classList.add('text-input-validation-error');
			warningMessage.classList.remove('hidden');
			saveBtn.setAttribute('disabled', '');
		} else {
			input.classList.remove('text-input-validation-error');
			warningMessage.classList.add('hidden');
			saveBtn.removeAttribute('disabled');
		}
	}
}

function salesmanagoValidateDoiEmailId()
{
	let smDoiEmailIdValue = document.getElementById( "double-opt-in-email-id" ).value

	return smDoiEmailIdValue.length === 36 || smDoiEmailIdValue.length === 0
}
function salesmanagoIsDoiEmailIdTemplateId()
{
	let smDoiEmailId = document.getElementById( "double-opt-in-email-id" )
	if ( ! ( smDoiEmailId.value ) ) {
		return false
	}
	return smDoiEmailId.value === smDoiEmailId.dataset.legacyTemplateId
}

function salesmanagoTestCartRecovery(url)
{
	document.getElementById( 'salesmanago-cart-recovery-test' ).style.display = "table-row";
	let smOutput = document.getElementById( 'salesmanago-cart-recovery-test-content' );

	let smXhttp                = new XMLHttpRequest();
	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200) {
			smOutput.innerHTML = this.responseText;
		} else if (this.readyState === 4 && this.status !== 200) {
			smOutput.innerHTML =
				'<p style="color: #ff0000">' + _t( 'problem' ) + '</p><u>'
				+ url + "</u> " + _t( 'urlNotFound' ) + " " + this.status;
		} else if (this.readyState >= 1) {
			smOutput.innerHTML = this.readyState + '/' + this.status
		}
	};
	smXhttp.open( "GET", url + '&test=1', true );
	smXhttp.send();
}

function salesmanagoRefreshOwnerList()
{
	let smSelect  = document.getElementById( 'salesmanago-owner' )
	let smSuccess = document.getElementById( 'salesmanago-refresh-owner-success' )

	let smData = new FormData();
	smData.append( 'action', 'salesmanago_refresh_owners' );

	let smXhttp                = new XMLHttpRequest();
	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200) {
			smSelect.innerHTML = this.responseText;
			smSuccess.children.item( 0 ).classList.add( 'checkmark_stem_success' )
			smSuccess.children.item( 1 ).classList.add( 'checkmark_kick_success' )
			smSuccess.classList.remove( 'hidden' )
			setTimeout( function ()  {smSuccess.classList.add( 'hidden' )}, 5000 )
		} else if (this.readyState === 4 && this.status !== 200) {
			smSuccess.innerHTML = "Failed to reload list. Try to log out and log into SALESmanago plugin.";
			smSuccess.classList.remove( 'refresh-owner-success' )
			smSuccess.classList.remove( 'hidden' )
			smSuccess.classList.add( 'error' )
		}
	}

	smXhttp.open( "POST", ajaxurl, true )
	smXhttp.send( smData )
}

function insertAfter(newNode, referenceNode) {
	referenceNode.parentNode.insertBefore( newNode, referenceNode.nextSibling );
}

function salesmanagoToggleDisableMonitCode()
{
	let smMonitCodeInputs = document.getElementsByClassName( "monitcode-wrapper" );
	for (let i = 0; i < smMonitCodeInputs.length; i++) {
		smMonitCodeInputs[i].classList.toggle( "hidden" );
	}
}

function salesmanagoTestFunctionToGenerateSwJs()
{
	document.getElementById( 'salesmanago-generate-sw-js-test' ).style.display = "table-row";
	let smTestContent = document.getElementById( 'salesmanago-generate-sw-js-test-content' );
	let smData        = new FormData();
	smData.append( 'action', 'salesmanago_generate_swjs' );

	let smXhttp                = new XMLHttpRequest();
	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200) {
			smTestContent.innerHTML = this.responseText;
		} else if (this.readyState === 4 && this.status !== 200) {
			smTestContent.innerHTML = "Connection from backend failed. Try to log out and log into SALESmanago plugin.";
		}
	}

	smXhttp.open( 'POST', ajaxurl, true );
	smXhttp.send( smData );
}

function salesmanagoToggleContactCookieTtl()
{
	document.getElementById('salesmanago-contact-cookie-ttl').parentNode.parentNode.classList.toggle('hidden');
}

function salesmanagoCookieTtlValidation()
{
	let input = document.getElementById('salesmanago-contact-cookie-ttl');
	let message = document.getElementById('salesmanago-contact-cookie-ttl-error-message');
	const regex = /(^$|^\d+)/;

	if (input.value < 0 || input.value > 3652 || !regex.test(input.value)) {
		input.classList.add('text-input-validation-error');
		message.classList.remove('hidden');
	} else {
		input.classList.remove('text-input-validation-error');
		message.classList.add('hidden');
	}
}
function copyApiV3EndpointToClipBoard()
{
	let inputElem = document.getElementById('api-v3-webhook-url-input');
	inputElem.select();
	document.execCommand("copy");
}

function salesmanagoLocationValidation()
{
	const input = document.getElementById('salesmanago-location');
	const message = document.getElementById('salesmanago-location-error');
	const regex = /^[a-zA-Z_][a-zA-Z0-9_]{2,35}$/;

	let saveBtn = document.getElementById("salesmanago-save-btn");

	if (!input.value || !regex.test(input.value)) {
		input.classList.add('text-input-validation-error');
		message.classList.remove('hidden');
		saveBtn.setAttribute('disabled', '');
	} else {
		input.classList.remove('text-input-validation-error');
		message.classList.add('hidden');
		saveBtn.removeAttribute('disabled');
	}
}
function salesmanagoValidateApiKey()
{
	const input = document.getElementById( 'api-v3-key-input' );
	const message = document.getElementById( 'sm-api-key-error' );
	const submitBtn = document.getElementById( 'sm-btn-submit-api-key' );
	const regex = /^[a-zA-Z0-9]{1,64}$/;
	if ( input.value && !regex.test( input.value ) ) {
		input.classList.add( 'text-input-validation-error' );
		message.classList.remove( 'hidden' );
		submitBtn.setAttribute( 'disabled', '' );
	} else {
		input.classList.remove( 'text-input-validation-error' );
		message.classList.add( 'hidden' );
		submitBtn.removeAttribute( 'disabled' );
	}
}
function salesmanagoCopyLog(name)
{
	event.preventDefault(); // Prevent reloading the page
	const aboutLog = document.getElementById(name);
	aboutLog.select();
	document.execCommand('copy');
}
function salesmanagoValidateCurrencyCode()
{
	const input = document.getElementById( 'sm-catalog-currency' );
	const message = document.getElementById( 'sm-catalog-currency-error' );
	const submitBtn = document.getElementById( 'sm-btn-create-catalog' );
	const regex = /^[a-zA-Z]{3}$/;

	if ( input.value && !regex.test( input.value ) ) {
		input.classList.add( 'text-input-validation-error' );
		message.classList.remove( 'hidden' );
		submitBtn.setAttribute( 'disabled', '' );
	} else {
		input.classList.remove( 'text-input-validation-error' );
		message.classList.add( 'hidden' );
		submitBtn.removeAttribute( 'disabled' );
	}
}

function salesmanagoRefreshCatalogList() {

	salesmanagoRotateAnimation('sm-refresh-catalogs');

	let smData = new FormData();
	smData.append( 'action', 'salesmanago_refresh_catalogs' );
	let smXhttp                = new XMLHttpRequest();
	smXhttp.onreadystatechange = function () {
		if (this.readyState === 4 && this.status === 200 && this.responseText !== 'ERR') {
			salesmanagoUpdateCatalogSelectElement(JSON.parse(this.responseText));
			salesmanagoShowRefreshCatalogNotice(true);
		} else if (this.readyState === 4 && ( this.status !== 200 || this.responseText === 'ERR')) {
			salesmanagoShowRefreshCatalogNotice(false);
		}
	}
	smXhttp.open( "POST", ajaxurl, true )
	smXhttp.send( smData );
}

function salesmanagoUpdateCatalogSelectElement( updatedCatalogs )
{
	let smSelect = document.getElementById( 'sm-product-catalog-select' );
	let selectedCatalog = smSelect.options[smSelect.selectedIndex];
	let noneOptionText =
		document.getElementsByTagName('html')[0].getAttribute('lang') === 'pl-PL' ?
			'Brak (nie synchronizuj produktów w czasie rzeczywistym) ' :
			'None (do not synchronize products in real-time)';

	let noneOption = '<option id="select-option-none" value>' +
		noneOptionText +
		'</option>';

	let result = '';
	for (catalog of updatedCatalogs) {
		let optionTag = '<option value="' + catalog['catalogId'] + '" ';
		if (catalog['catalogId'] === selectedCatalog.value){
			optionTag += 'selected';
		}
		optionTag += '>';
		result += optionTag + catalog['name'] + '</option>';
	}
	smSelect.innerHTML = noneOption + result;
}

function salesmanagoShowRefreshCatalogNotice( isSuccess )
{
	let noticeType = isSuccess ? 'success' : 'fail';
	let smNoticeIcon = document.getElementById('sm-refresh-catalog-' + noticeType);
	smNoticeIcon.classList.remove( 'hidden' )
	setTimeout( function ()  {smNoticeIcon.classList.add( 'hidden' )}, 3000 );
}

function salesmanagoRotateAnimation(id)
{
	let el = document.getElementById(id);
	el.classList.add('sm-rotating');
	// Recreate the element to enable multiple animations
	let newEl = el.cloneNode(true);
	el.parentNode.replaceChild(newEl, el);
}
