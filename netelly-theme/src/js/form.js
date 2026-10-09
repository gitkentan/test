/*
 * Contact form UX on top of Snow Monkey Forms (README 9).
 * - Blur validation (required / email), errors in ink + ⚠ under the field.
 * - Submit stays disabled until the privacy consent is checked; 「送信中…」 while sending.
 * - Prefill: ?type=recruit selects 採用 (?position=… goes into the message), ?type=press selects 取材・プレス.
 * - Privacy page link inside the consent label; .btn classes on the plugin's buttons.
 * Texts come from data-* on .contact__form (theme strings / page fields).
 */

const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const INPUT_SCREENS = [ 'loading', 'input', 'back', 'invalid' ];

export function initForm() {
	const wrap = document.querySelector( '.contact__form' );
	const form = wrap?.querySelector( '.snow-monkey-form' );
	if ( ! form ) {
		return;
	}
	const t = wrap.dataset;
	const touched = new Set();
	let clicked = null;

	const onInputScreen = () => INPUT_SCREENS.includes( form.dataset.screen );
	const rulesOf = ( el ) => ( el.dataset.validations || '' ).split( /\s+/ ).filter( Boolean );

	const fieldOf = ( el ) => el.closest( '.smf-item' );

	const errorFor = ( el ) => {
		const group = el.closest( '[data-validations]' );
		const rules = rulesOf( group );
		if ( ! rules.length ) {
			return '';
		}
		if ( 'radio' === el.type || 'checkbox' === el.type ) {
			const checked = group.querySelector( 'input:checked' );
			return rules.includes( 'required' ) && ! checked ? t.errorRequired : '';
		}
		const value = el.value.trim();
		if ( rules.includes( 'required' ) && ! value ) {
			return t.errorRequired;
		}
		if ( rules.includes( 'email' ) && value && ! EMAIL.test( value ) ) {
			return t.errorEmail;
		}
		return '';
	};

	const show = ( el, message ) => {
		const item = fieldOf( el );
		if ( ! item ) {
			return;
		}
		const name = ( el.name || '' ).replace( /\[\]$/, '' );
		const id = `${ name }--error`;
		let box = item.querySelector( '.form-error' );
		const target = el.closest( '[data-validations]' );
		if ( message ) {
			if ( ! box ) {
				box = document.createElement( 'p' );
				box.className = 'form-error';
				box.id = id;
				item.querySelector( '.smf-item__controls' ).append( box );
			}
			box.textContent = message;
			target.setAttribute( 'aria-invalid', 'true' );
			const described = ( target.getAttribute( 'aria-describedby' ) || '' ).split( ' ' ).filter( ( v ) => v && v !== id );
			target.setAttribute( 'aria-describedby', [ ...described, id ].join( ' ' ) );
		} else {
			box?.remove();
			target.removeAttribute( 'aria-invalid' );
		}
	};

	const validate = ( el ) => {
		const message = errorFor( el );
		show( el, message );
		return ! message;
	};

	const controls = () => [ ...form.querySelectorAll( '.smf-form [data-validations]' ) ]
		.map( ( group ) => ( group.matches( 'input, textarea, select' ) ? group : group.querySelector( 'input' ) ) )
		.filter( Boolean );

	const consent = () => form.querySelector( '.smf-checkboxes-control input[type="checkbox"]' );

	const updateSubmit = () => {
		const button = form.querySelector( '.smf-action [data-action="confirm"], .smf-action [data-action="complete"]' );
		const box = consent();
		if ( button && box && onInputScreen() ) {
			button.disabled = ! box.checked;
		}
	};

	const decorate = () => {
		// Radio group name (role=radiogroup) from the item label.
		form.querySelectorAll( '.smf-item' ).forEach( ( item, i ) => {
			const group = item.querySelector( '[role="radiogroup"], [role="group"]' );
			const label = item.querySelector( '.smf-item__label__text' );
			if ( group && label ) {
				label.id ||= `smf-label-${ i }`;
				group.setAttribute( 'aria-labelledby', label.id );
			}
		} );

		// Privacy page link inside the consent label.
		const text = consent()?.closest( 'label' )?.querySelector( '.smf-checkbox-control__label' );
		if ( text && t.privacyUrl && t.privacyTitle && ! text.querySelector( 'a' ) && text.textContent.includes( t.privacyTitle ) ) {
			const [ before, ...rest ] = text.textContent.split( t.privacyTitle );
			const a = document.createElement( 'a' );
			a.href = t.privacyUrl;
			a.target = '_blank';
			a.rel = 'noopener';
			a.textContent = t.privacyTitle;
			text.replaceChildren( before, a, rest.join( t.privacyTitle ) );
		}

		// Plugin buttons → theme buttons (arrow animates on hover; clicks still hit the button).
		form.querySelectorAll( '.smf-button-control__control' ).forEach( ( button ) => {
			const back = 'back' === button.dataset.action;
			button.classList.add( 'btn', back ? 'btn--outline' : 'btn--primary' );
			const node = [ ...button.childNodes ].find( ( n ) => 3 === n.nodeType && /[→↗]\s*$/.test( n.textContent ) );
			if ( node ) {
				const m = node.textContent.match( /^([\s\S]*?)\s*([→↗])\s*$/ );
				const arrow = document.createElement( 'span' );
				arrow.className = 'arrow';
				arrow.setAttribute( 'aria-hidden', 'true' );
				arrow.textContent = m[ 2 ];
				// Design sets 「内容を確認する →」 as one text run: keep the space (no flex gap, see form.css).
				node.textContent = `${ m[ 1 ].trim() }\u00a0`;
				node.after( arrow );
			}
		} );

		updateSubmit();
	};

	const prefill = () => {
		const params = new URLSearchParams( location.search );
		// ?type=recruit (careers) / ?type=press (press kit) → preselect that inquiry type.
		const want = { recruit: t.recruit, press: t.press }[ params.get( 'type' ) ];
		if ( want ) {
			const radio = [ ...form.querySelectorAll( 'input[type="radio"]' ) ].find( ( r ) => r.value === want );
			if ( radio ) {
				radio.checked = true;
			}
		}
		const position = params.get( 'position' );
		const message = form.querySelector( 'textarea' );
		if ( position && message && ! message.value && t.position ) {
			message.value = `${ t.position.replace( '%s', position.slice( 0, 200 ) ) }\n\n`;
		}
	};

	form.addEventListener( 'focusout', ( e ) => {
		const el = e.target;
		if ( onInputScreen() && el.matches( 'input:not([type="hidden"]), textarea' ) && el.closest( '[data-validations]' ) ) {
			touched.add( el.name );
			validate( el );
		}
	} );

	[ 'input', 'change' ].forEach( ( type ) => form.addEventListener( type, ( e ) => {
		const el = e.target;
		if ( el === consent() ) {
			updateSubmit();
		}
		if ( touched.has( el.name ) || 'radio' === el.type || 'checkbox' === el.type ) {
			if ( el.closest( '[data-validations]' ) && ( touched.has( el.name ) || form.querySelector( `#${ CSS.escape( el.name.replace( /\[\]$/, '' ) ) }--error` ) ) ) {
				validate( el );
			}
		}
	} ) );

	form.addEventListener( 'click', ( e ) => {
		clicked = e.target.closest( '.smf-button-control__control' ) || clicked;
	}, true );

	// Validate everything before the plugin posts the input screen (capture runs first).
	wrap.addEventListener( 'submit', ( e ) => {
		if ( ! onInputScreen() ) {
			return;
		}
		const invalid = controls().filter( ( el ) => {
			touched.add( el.name );
			return ! validate( el );
		} );
		if ( invalid.length ) {
			e.preventDefault();
			e.stopPropagation();
			invalid[ 0 ].focus();
		}
	}, true );

	// GA4 conversion (inc/seo.php loads gtag when an ID is set): inquiry type of the sent form.
	let inquiryType = '';
	form.addEventListener( 'smf.complete', () => {
		window.gtag?.( 'event', 'generate_lead', { form_name: 'contact', inquiry_type: inquiryType, language: document.documentElement.lang } );
	} );

	form.addEventListener( 'smf.beforesubmit', () => {
		inquiryType = form.querySelector( '[name="type"]:checked, input[type="hidden"][name="type"]' )?.value || inquiryType;
		const button = clicked || form.querySelector( '.smf-action [type="submit"]' );
		clicked = null;
		if ( button && 'back' !== button.dataset.action ) {
			button.classList.add( 'is-sending' );
			button.setAttribute( 'aria-live', 'polite' );
			button.replaceChildren( t.sending );
		}
	} );

	[ 'smf.input', 'smf.back', 'smf.invalid', 'smf.confirm', 'smf.complete', 'smf.systemerror' ].forEach( ( type ) => {
		form.addEventListener( type, () => {
			if ( 'smf.invalid' === type ) {
				form.querySelectorAll( '.form-error' ).forEach( ( el ) => el.remove() );
			}
			decorate();
		} );
	} );

	prefill();
	decorate();
}
