import ApiService from "/src/services/ApiService";

export default class WorkingRuleService extends ApiService {
  static getByMonth(params: string) {
    const url = this.getBaseUrl() + "working-rule/" + params;
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers,
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}
