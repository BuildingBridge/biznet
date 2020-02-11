

const ScrollDown = {
	props: [],
	components: {},
	directives: {
		scroll: {
			inserted: function (el, binding) {
				let f = function (evt) {
					if (binding.value(evt, el))
					{
						el.previousElementSibling.removeEventListener('scroll', f);
					}
				};
				el.previousElementSibling.addEventListener('scroll', f);
			}
		},
		resize: {
			inserted: function (el, binding) {
				let f = function () {
					if (binding.value())
					{
						window.removeEventListener('resize', f);
					}
				};
				window.addEventListener('resize', f);
			}
		}
	},
	template: `
		<div class="b24-form-scroll-down"
			v-scroll="handleScroll"
			v-resize="handleResize"
			@click="scrollDown()"
		>
			<p class="b24-form-scroll-down-text">Прочитайте до конца</p>
			<div class="b24-form-scroll-down-arrow">
				<div class="b24-form-scroll-down-arrow-item"></div>
				<div class="b24-form-scroll-down-arrow-item"></div>
				<div class="b24-form-scroll-down-arrow-item"></div>
			</div>
		</div>
	`,
	mounted: function ()
	{
		setTimeout(() => {
			this.handleResize();
		}, 0);
		this.$root.$on('resize', this.handleResize);
	},
	methods: {
		scrollDown()
		{
			let el = this.$el.previousElementSibling;
			let interval = 10;
			let duration = 100;

			let diff = (el.scrollHeight - el.offsetHeight - el.scrollTop);
			let step = diff / (duration / interval);
			let scroller = () => {
				diff -= step;
				el.scrollTop += step;
				if (diff > 0)
				{
					setTimeout(scroller, interval);
				}
			};
			scroller();
		},
		handleResize()
		{
			let el = this.$el.previousElementSibling;
			let visible = (el.scrollHeight - el.clientHeight) > 10;
			if (visible)
			{
				this.handleScroll(null, this.$el);
			}
			else
			{
				this.$el.style.display = visible ? '' : 'none';
			}
		},
		handleScroll(event, node)
		{
			let el = node.previousElementSibling;
			let diff = el.scrollHeight - el.scrollTop - el.clientHeight;

			let opacity = diff / 400;
			if (opacity > 0.4)
			{
				node.style.opacity = opacity > 1 ? 1 : opacity;
			}
			node.style.display = !diff ? 'none' : '';
		}
	},
};

export {
	ScrollDown,
}