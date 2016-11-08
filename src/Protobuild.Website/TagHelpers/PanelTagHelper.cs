using System.Threading.Tasks;
using Microsoft.AspNetCore.Razor.TagHelpers;

namespace Protobuild.Website.TagHelpers
{
    public class PanelTagHelper : TagHelper
    {
        [HtmlAttributeName("type")]
        public string Type { get; set; }

        [HtmlAttributeName("heading")]
        public string Heading { get; set; }

        [HtmlAttributeName("nobody")]
        public bool NoBody { get; set; }

        public override async Task ProcessAsync(TagHelperContext context, TagHelperOutput output)
        {
            output.TagName = "div";
            output.Attributes.Add("class", "panel panel-" + (string.IsNullOrWhiteSpace(Type) ? "default" : Type));
            output.Content.AppendHtml("<div class=\"panel-heading\">");
            output.Content.Append(Heading ?? string.Empty);
            output.Content.AppendHtml("</div>");

            if (!NoBody)
            {
                output.Content.AppendHtml("<div class=\"panel-body\">");
            }

            var children = await output.GetChildContentAsync();
            output.Content.AppendHtml(children.GetContent());

            if (!NoBody)
            {
                output.Content.AppendHtml("</div>");
            }
        }
    }
}
