using Microsoft.AspNetCore.Razor.TagHelpers;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc.ViewFeatures;
using Microsoft.AspNetCore.Mvc.Rendering;

namespace Protobuild.Website.TagHelpers
{
    public class FooterTagHelper : TagHelper
    {
        [ViewContext]
        public ViewContext ViewContext { get; set; }

        public override void Process(TagHelperContext context, TagHelperOutput output)
        {
            output.TagName = "p";

            var session = ViewContext.HttpContext.Session;

            if (session.GetInt32("IsAuthenticated") == 1)
            {
                output.Content.Append("Logged in as ");
                output.Content.Append(session.GetString("RealName"));
                output.Content.AppendHtml(" &bull; ");
                output.Content.AppendHtml(@"<a href=""/hach-que"">My Account</a>");
                output.Content.AppendHtml(" &bull; ");
                output.Content.AppendHtml(@"<a href=""/logout"">Logout</a>");
            }
            else
            {
                output.Content.AppendHtml(@"<a href=""/login"">Login</a>");
            }
            
            output.Content.AppendHtml(ViewContext.HttpContext.Session.GetString("test"));
        }
    }
}
