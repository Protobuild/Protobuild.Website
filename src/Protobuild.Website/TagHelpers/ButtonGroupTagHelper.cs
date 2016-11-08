using Microsoft.AspNetCore.Razor.TagHelpers;
using System.Threading.Tasks;

namespace Protobuild.Website.TagHelpers
{
    public class ButtonGroupTagHelper : TagHelper
    {
        public override async Task ProcessAsync(TagHelperContext context, TagHelperOutput output)
        {
            var children = await output.GetChildContentAsync();

            output.TagName = null;
            output.TagMode = TagMode.StartTagAndEndTag;

            if (string.IsNullOrWhiteSpace(children.GetContent().Trim()))
            {
                output.SuppressOutput();
                return;
            }

            output.Content.AppendHtml("<div class=\"btn-group\">");
            output.Content.AppendHtml(children.GetContent());
            output.Content.AppendHtml("</div>");
            output.Content.AppendHtml("<br /><br />");
        }
    }
}
