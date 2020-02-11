import * as ScrollLock from './scroll-lock/scroll-lock';
import {MoveObserver} from './moveobserver';

const Scroll = {
	items: [],
	toggle (element, mode)
	{
		mode ? this.enable(element) : this.disable(element);
	},
	getLastItem ()
	{
		return this.items.length > 0 ? this.items[this.items.length - 1] : null;
	},
	disable (element)
	{
		let prevElement = this.getLastItem();
		if (prevElement)
		{
			ScrollLock.addLockableTarget(prevElement);
			ScrollLock.addFillGapTarget(prevElement);
		}
		ScrollLock.disablePageScroll(element);
		this.items.push(element);
	},
	enable ()
	{
		setTimeout(() => {
			let element = this.items.pop();
			ScrollLock.enablePageScroll(element);
			let prevElement = this.getLastItem();
			if (prevElement)
			{
				ScrollLock.removeFillGapTarget(prevElement);
				ScrollLock.removeLockableTarget(prevElement);
			}
		}, 300);
	},
};

const Type = {
	defined(val): Boolean
	{
		return typeof val !== 'undefined';
	},
	object(val): Boolean
	{
		return typeof val === 'object';
	},
	string(val): Boolean
	{
		return typeof val === 'string';
	},
};

const Conv = {
	number (value): Number
	{
		value = parseFloat(value);
		return isNaN(value) ? 0 : value;
	},
	string (): String
	{

	},
	formatMoney(val: Number, format): String
	{
		return (format || '').replace('#', val || 0);
	},
};

const Color = {
	parseHex (hex)
	{
		hex = this.fillHex(hex);
		let parts = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})?$/i.exec(hex);
		if (!parts)
		{
			parts = [0,0,0,1];
		}
		else
		{
			parts = [
				parseInt(parts[1], 16),
				parseInt(parts[2], 16),
				parseInt(parts[3], 16),
				parseInt(100 * (parseInt(parts[4] || 'ff', 16) / 255)) / 100,
			];
		}

		return parts;
	},
	hexToRgba (hex)
	{
		return 'rgba(' + this.parseHex(hex).join(', ') + ')';
	},
	toRgba (numbers)
	{
		return 'rgba(' + numbers.join(', ') + ')';
	},
	fillHex (hex, fillAlpha)
	{
		if (hex.length === 4 || (fillAlpha && hex.length === 5))
		{
			hex = hex.replace(/([a-f0-9])/gi, "$1$1");
		}

		if (fillAlpha && hex.length === 7)
		{
			hex += 'ff';
		}

		return hex;
	},
	isHexDark (hex)
	{
		hex = this.parseHex(hex);
		const r = hex[0]; const g = hex[1]; const b = hex[2];
		const brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
		return brightness < 155;
	},
};

const Render = {
	component()
	{

	}
};

export {
	Type,
	Conv,
	Color,
	Render,
	Scroll,
	MoveObserver,
}