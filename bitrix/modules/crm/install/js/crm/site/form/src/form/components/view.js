import {Form} from "./form";
import * as Window from "../../window/registry";
import * as Util from "../../util/registry";

const Wrapper = {
	props: ['form'],
	data: function () {
		return {
			designStyleNode: null,
		};
	},
	methods: {
		classes()
		{
			let list = [];
			if (this.form.design.isDark())
			{
				list.push('b24-form-dark');
			}
			else if (this.form.design.isAutoDark())
			{
				//list.push('b24-form-dark-auto');
			}

			if (this.form.design.style)
			{
				list.push('b24-form-style-' + this.form.design.style);
			}
			return list;
		},
		isDesignStylesApplied()
		{
			let color = this.form.design.color;
			let css = [];

			let fontFamily = this.form.design.getFontFamily();
			if (fontFamily)
			{
				fontFamily = fontFamily.trim();
				fontFamily = fontFamily.indexOf(' ') > 0
					? `"${fontFamily}"`
					: fontFamily;
				css.push('--b24-font-family: ' + fontFamily + ', var(--b24-font-family-default);');
			}
			let fontUri = this.form.design.getFontUri();
			if (fontUri)
			{
				let link = document.createElement('LINK');
				link.setAttribute('href', fontUri);
				link.setAttribute('rel', 'stylesheet');
				document.head.appendChild(link);
			}

			let colorMap = {
				style: '--b24-font-family',
				primary: '--b24-primary-color',
				primaryText: '--b24-primary-text-color',
				primaryHover: '--b24-primary-hover-color',
				text: '--b24-text-color',
				background: '--b24-background-color',
				fieldBorder: '--b24-field-border-color',
				fieldBackground: '--b24-field-background-color',
				fieldFocusBackground: '--b24-field-focus-background-color',
			};
			for (let key in color)
			{
				if (!color.hasOwnProperty(key) || !color[key])
				{
					continue;
				}
				if (!colorMap.hasOwnProperty(key) || !colorMap[key])
				{
					continue;
				}

				let rgba = Util.Color.hexToRgba(color[key]);
				css.push(colorMap[key] + ': ' + rgba + ';');
			}
			let primaryHover = Util.Color.parseHex(color.primary);
			primaryHover[3] -= 0.3;
			primaryHover = Util.Color.toRgba(primaryHover);
			css.push(colorMap.primaryHover + ': ' + primaryHover + ';');

			/*
			if (this.form.view.type === 'inline' && this.form.design.shadow)
			{
				(document.documentElement.clientWidth <= 500)
					? css.push('padding: 3px;')
					: css.push('padding: 20px;')
			}
			*/

			css = css.join("\n");

			if (!this.designStyleNode)
			{
				this.designStyleNode = document.createElement('STYLE');
				this.designStyleNode.setAttribute('type', 'text/css');
			}

			if (css)
			{
				css = `.b24-form #b24-${this.form.getId()}, .b24-form #b24-${this.form.getId()}.b24-form-dark
				 {
					${css}
				}`;

				this.designStyleNode.textContent = '';
				this.designStyleNode.appendChild(document.createTextNode(css));
				document.head.appendChild(this.designStyleNode);
				return true;
			}

			if (!css)
			{
				if (this.designStyleNode && this.designStyleNode.parentElement)
				{
					this.designStyleNode.parentElement.removeChild(this.designStyleNode);
				}
				return false;
			}
		}
	},
	template: `
		<div class="b24-form">
			<div
			 	:class="classes()"
				:id="'b24-' + form.getId()"
				:data-styles-apllied="isDesignStylesApplied()"
			>
				<slot></slot>
			</div>
		</div>
	`
};

const viewMixin = {
	props: ['form'],
	components: Object.assign(
		Window.Components.Definition,
		{
			'b24-form-container': Wrapper
		}
	),
};

const Inline = {
	mixins: [viewMixin],
	template: `
		<b24-form-container :form="form" v-show="form.visible">
			<slot></slot>
		</b24-form-container>
	`
};
const Popup = {
	mixins: [viewMixin],
	template: `
		<b24-form-container :form="form">
			<b24-popup v-bind:key="form.id" 
				v-bind:show="form.visible"
				v-bind:position="form.view.position"  
				@hide="form.hide()"
			>
				<div v-if="form.view.title" class="b24-window-header">
					<h2 class="b24-window-header-title">{{ form.view.title }}</h2>
				</div>
				<slot></slot>
			</b24-popup>
		</b24-form-container>
	`,
};

const Panel = {
	mixins: [viewMixin],
	template: `
		<b24-form-container :form="form">
			<b24-panel v-bind:key="form.id" 
				v-bind:show="form.visible" 
				v-bind:position="form.view.position" 
				v-bind:vertical="form.view.vertical" 
				@hide="form.hide()"
			>
				<div v-if="form.view.title" class="b24-window-header">
					<h2 class="b24-window-header-title">{{ form.view.title }}</h2>
				</div>
				<slot></slot>
			</b24-panel>
		</b24-form-container>
	`,
};

const Widget = {
	mixins: [viewMixin],
	template: `
		<b24-form-container :form="form">
			<b24-widget v-bind:key="form.id" 
				v-bind:show="form.visible" 
				v-bind:position="form.view.position" 
				v-bind:vertical="form.view.vertical" 
				@hide="form.hide()"
			>
				<slot></slot>
			</b24-widget>
		</b24-form-container>
	`,
};

const Definition = {
	'b24-form': Form,
	'b24-form-inline': Inline,
	'b24-form-panel': Panel,
	'b24-form-popup': Popup,
	'b24-form-widget': Widget,
};

export {
	Inline,
	Popup,
	Panel,
	Widget,
	Definition
}