(() => {
	const settings = window.ictuVietQrSettings;
	if (!settings || !window.wp?.data) {
		return;
	}

	const updateQr = () => {
		const radio = document.querySelector('input.wc-block-components-radio-control__input[value="bacs"]');
		const content = radio?.closest('.wc-block-components-radio-control-accordion-option')?.querySelector('.wc-block-components-radio-control-accordion-content');
		if (!radio || !content) {
			return;
		}

		const description = content.firstElementChild;
		const descriptionText = 'Quét mã VietQR bên dưới để chuyển khoản. Nội dung chuyển khoản đã được tạo sẵn; vui lòng giữ nguyên nội dung này.';
		if (description && description.textContent !== descriptionText) {
			description.textContent = descriptionText;
		}

		let container = content.querySelector('.ictu-vietqr-payment');
		if (!container) {
			container = document.createElement('div');
			container.className = 'ictu-vietqr-payment';
			content.append(container);
		}

		container.hidden = !radio.checked;
		if (!radio.checked) {
			return;
		}

		if (settings.error) {
			container.textContent = settings.error;
			return;
		}

		const cart = window.wp.data.select('wc/store/cart');
		const totals = cart?.getCartTotals?.();
		const minorUnit = Number(totals?.currency_minor_unit || 0);
		const amount = Math.round(Number(totals?.total_price) / (10 ** minorUnit));
		if (!Number.isFinite(amount) || amount <= 0) {
			container.textContent = 'Không thể xác định tổng tiền để tạo mã QR.';
			return;
		}

		const imageUrl = new URL(settings.imageBaseUrl);
		imageUrl.searchParams.set('amount', String(amount));
		imageUrl.searchParams.set('addInfo', settings.reference);
		if (settings.accountName) {
			imageUrl.searchParams.set('accountName', settings.accountName);
		}

		if (container.querySelector('img')?.src === imageUrl.toString()) {
			return;
		}

		container.replaceChildren();
		const title = document.createElement('p');
		title.textContent = 'Quét mã VietQR để chuyển khoản:';

		const image = document.createElement('img');
		image.src = imageUrl.toString();
		image.alt = 'Mã QR chuyển khoản VietQR';
		image.width = 280;
		image.height = 280;
		image.loading = 'lazy';
		image.decoding = 'async';
		image.referrerPolicy = 'no-referrer';

		const reference = document.createElement('p');
		reference.append('Nội dung chuyển khoản: ');
		const referenceValue = document.createElement('strong');
		referenceValue.textContent = settings.reference;
		reference.append(referenceValue);

		const notice = document.createElement('p');
		notice.textContent = 'Đơn hàng chỉ được xác nhận sau khi cửa hàng đối soát khoản chuyển tiền.';

		container.append(title, image, reference, notice);
	};

	updateQr();
	window.wp.data.subscribe(updateQr);
	document.addEventListener('change', updateQr);
	new MutationObserver((mutations) => {
		if (mutations.some((mutation) => !mutation.target.closest?.('.ictu-vietqr-payment'))) {
			updateQr();
		}
	}).observe(document.body, { childList: true, subtree: true });
})();
