{if $wcfServerList|count}
	<section class="section">
		<h2 class="sectionTitle">{lang}shop.acp.product.version.packagesniffer{/lang}</h2>

		<dl{if $errorField == 'packagesnifferAvailability'} class="formError"{/if}>
			<dt><label for="packagesnifferAvailability">{lang}shop.acp.product.version.packagesniffer.availability{/lang}</label></dt>
			<dd>
				<input type="checkbox" id="packagesnifferAvailability" name="packagesnifferAvailability" value="1"{if !$packagesnifferAvailability|empty} checked="checked"{/if} />

				{if $errorField == 'packagesnifferAvailability'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}shop.acp.product.version.packagesniffer.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
	</section>
{/if}
