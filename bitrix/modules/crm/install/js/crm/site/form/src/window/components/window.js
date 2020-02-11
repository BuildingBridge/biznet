import {ScrollDown} from "./scrolldown";
import {Scroll} from "../../util/registry";

const Overlay = {
	props: ['show'],
	components: {},
	template: `
		<transition name="b24-a-fade" appear>
			<div class="b24-window-overlay" 
				@click="$emit('click')"
				v-show="show"
			></div>
		</transition>
	`,
};

let windowMixin = {
	props: ['show', 'title', 'position', 'vertical', 'maxWidth', 'zIndex'],
	components: {
		'scroll-down': ScrollDown,
		'b24-overlay': Overlay,
	},
	data: function ()
	{
		return {
			showed: false,
			escHandler: null
		};
	},
	methods: {
		hide() {
			this.show = false;
			this.$emit('hide');
		},
		listenEsc()
		{
			if (!this.escHandler)
			{
				this.escHandler = e => {
					if (this.show && e.key === 'Escape')
					{
						e.preventDefault();
						e.stopPropagation();

						this.hide();
					}
				};
			}

			this.show
				? document.addEventListener('keydown', this.escHandler)
				: document.removeEventListener('keydown', this.escHandler);
		},
		toggleScroll ()
		{
			if (!this.scrollable)
			{
				return;
			}

			Scroll.toggle(
				this.$el.querySelector('.b24-window-scrollable'),
				!this.show
			);
		},
	},
	mounted ()
	{
		this.listenEsc();
		if (this.show)
		{
			this.toggleScroll();
		}
	},
	watch: {
		show (val)
		{
			if (val && !this.showed)
			{
				this.showed = true;
			}

			this.listenEsc();
			this.toggleScroll();
		},
	}
};

const Popup = {
	mixins: [windowMixin],
	data: function () {
		return {
			scrollable: true,
		};
	},
	template: `
		<div class="b24-window">
			<b24-overlay :show="show" @click="hide()"></b24-overlay>
			<transition :name="getTransitionName()" appear>
				<div class="b24-window-popup" 
					:class="classes()"
					@click.self.prevent="hide()"
					v-show="show"
				>
					<div class="b24-window-popup-wrapper" 
						:style="{ maxWidth: maxWidth + 'px' }"
					>
						<div class="b24-window-scrollable" :style="{ zIndex: zIndex, height: 'auto' }">
							<button @click="hide()" type="button" class="b24-window-close"></button>
							<header v-if="title" class="b24-window-popup-head">
								<h2 class="b24-window-popup-title">{{ title }}</h2>
							</header>
							<section class="b24-window-popup-body">
								<slot></slot>
							</section>
						</div>
					</div>
				</div>
			</transition>	
		</div>
	`,
	methods: {
		getTransitionName() {
			return 'b24-a-slide-' + (this.vertical || 'bottom');
		},
		classes: function () {
			return [
				'b24-window-popup-p-' + (this.position || 'center')
			];
		},
	},
};

const Panel = {
	mixins: [windowMixin],
	data: function () {
		return {
			scrollable: true,
		};
	},
	template: `
		<div class="b24-window">
			<b24-overlay :show="show" @click="hide()"></b24-overlay>
			<transition :name="getTransitionName()" appear>
				<div class="b24-window-panel"
					:class="classes()"
					v-show="show"
				>
						<button @click="hide()" type="button" class="b24-window-close"></button>
						<div class="b24-window-scrollable">
							<slot></slot>
						</div>
				</div>
			</transition>
		</div>
	`,
	methods: {
		getTransitionName() {
			return 'b24-a-slide-' + (this.vertical || 'bottom');
		},
		classes() {
			return [
				'b24-window-panel-pos-' + (this.position || 'right')
			];
		},
	},
};

const Widget = {
	mixins: [windowMixin],
	template: `
		<div class="b24-window">
			<transition :name="getTransitionName()" appear>
				<div class="b24-window-widget" 
					:class="classes()" 
					v-show="show"
				>
					<button @click="hide()" type="button" class="b24-window-close"></button>
					<div class="b24-window-widget-body">
						<slot></slot>
					</div>
				</div>
			</transition>
		</div>
	`,
	methods: {
		getTransitionName() {
			return 'b24-a-slide-short-' + (this.vertical || 'bottom');
		},
		classes() {
			return [
				'b24-window-widget-p-'
				+ (this.vertical || 'bottom') + '-'
				+ (this.position || 'right')
			];
		},
	},
};

const Definition = {
	'b24-overlay': Overlay,
	'b24-popup': Popup,
	'b24-panel': Panel,
	'b24-widget': Widget,
};

export {
	Overlay,
	Popup,
	Panel,
	Widget,
	Definition
}