export default interface ServerWorkingMonth {
  'id': number;
  'user_id': number;
  'month': string;
  'saldo_hours': number;
  'saldo_minutes': number;
  'saldo_positive': boolean;
  'saldo_capped': boolean;
  'finalized': boolean;
  'archived': boolean;
  'carried': boolean;

}