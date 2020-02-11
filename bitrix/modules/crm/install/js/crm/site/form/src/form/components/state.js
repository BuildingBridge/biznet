const StateBlock = {
	props: ['form'],
	template: `
		<section class="b24-form-state-container">
				<transition name="b24-a-fade">
					<section v-show="form.loading" class="b24-form-loader">
						<div class="b24-form-loader-icon"></div>
					</section>
				</transition>
				
				<section v-show="form.sent" class="b24-form-success">
					<div class="b24-form-success-inner">
						<div class="b24-form-success-icon"></div>
						<div class="b24-form-success-text">
							<p>{{ form.messages.get('stateSuccessTitle') }}</p>
							<p>{{ form.stateText }}</p>
						</div>
						<button class="b24-form-btn b24-form-btn-border b24-form-btn-tight"
							v-if="form.stateButton.text" 
							@click="form.stateButton.handler" 
						>
							{{ form.stateButton.text }}						
						</button>
					</div>
				</section>
			
				<section v-show="form.error" class="b24-form-error">
					<div class="b24-form-error-inner">
						<div class="b24-form-error-icon"></div>
						<div class="b24-form-error-text">
							<p>{{ form.stateText }}</p>
						</div>
					</div>
				</section>
				
				<section v-show="form.disabled" class="b24-form-warning">
					<div class="b24-form-warning-inner">
						<div class="b24-form-warning-icon"></div>
						<div class="b24-form-warning-text">
							<p>{{ form.messages.get('stateDisabled') }}</p>
						</div>
					</div>
				</section>
		</section>
	`,
	computed: {

	},
	methods: {

	}
};

export {
	StateBlock,
}