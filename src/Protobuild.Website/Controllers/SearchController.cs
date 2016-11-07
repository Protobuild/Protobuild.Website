using Microsoft.AspNetCore.Mvc;

namespace Protobuild.Website.Controllers
{
    public class SearchController : Controller
    {
        [Route("/search")]
        public IActionResult Index(string q)
        {
            return View(q);
        }
    }
}
