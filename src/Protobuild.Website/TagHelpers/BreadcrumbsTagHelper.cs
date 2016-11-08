using Microsoft.AspNetCore.Mvc.Rendering;
using Microsoft.AspNetCore.Mvc.ViewFeatures;
using Microsoft.AspNetCore.Razor.TagHelpers;
using Protobuild.Website.Models;
using System;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Protobuild.Website.TagHelpers
{
    [HtmlTargetElement("breadcrumb", ParentTag = "breadcrumbs")]
    public class BreadcrumbTagHelper : TagHelper
    {
        public override async Task ProcessAsync(TagHelperContext context, TagHelperOutput output)
        {
            var targetList = (List<Tuple<string, string>>)context.Items["breadcrumbs"];
            targetList.Add(new Tuple<string, string>(
                (string)context.AllAttributes["name"].Value,
                (string)context.AllAttributes["url"].Value));
            output.SuppressOutput();
        }
    }

    [RestrictChildren("breadcrumb")]
    public class BreadcrumbsTagHelper : TagHelper
    {
        [ViewContext]
        public ViewContext ViewContext { get; set; }

        [HtmlAttributeName("user")]
        public UserModel User { get; set; }

        [HtmlAttributeName("package")]
        public PackageModel Package { get; set; }

        public override async Task ProcessAsync(TagHelperContext context, TagHelperOutput output)
        {
            var breadcrumbs = new List<Tuple<string, string>>();
            breadcrumbs.Add(new Tuple<string, string>("Package Index", "/index"));
            
            if (User != null)
            {
                breadcrumbs.Add(new Tuple<string, string>(User.CanonicalName, User.GetUrl()));
            }

            if (Package != null && User != null)
            {
                breadcrumbs.Add(new Tuple<string, string>(Package.Name, Package.GetUrl(User)));
            }

            context.Items.Add("breadcrumbs", breadcrumbs);

            await output.GetChildContentAsync();

            output.TagName = null;
            output.TagMode = TagMode.StartTagAndEndTag;

            output.Content.AppendHtml(@"
<form action=""/search"" method=""GET"" id=""breadcrumb-search-form"">
  <div id=""breadcrumb-search"" class=""input-group"">
    <input type=""text"" id=""search-packages"" class=""form-control"" placeholder=""Search"" name=""q"">
    <span class=""input-group-btn"">
      <button class=""btn btn-default"" type=""submit"">
        <span class=""glyphicon glyphicon-search"" aria-hidden=""true""></span>
      </button>
    </span>
  </div>
</form>
");

            output.Content.AppendHtml("<ol class=\"breadcrumb\">");

            foreach (var breadcrumb in breadcrumbs)
            {
                if (breadcrumb.Item2 != null)
                {
                    output.Content.AppendFormat(
                        "<li><a href=\"{0}\">{1}</a></li>",
                        new object[] {
                        breadcrumb.Item2,
                        breadcrumb.Item1
                        });
                }
                else
                {
                    output.Content.AppendFormat(
                        "<li>{0}</li>",
                        new object[] {
                        breadcrumb.Item1
                        });
                }
            }

            output.Content.AppendHtml("</ol>");
        }
    }
}
